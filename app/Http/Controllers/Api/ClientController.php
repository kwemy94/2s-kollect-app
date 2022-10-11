<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Repositories\ClientRepository;
use App\Repositories\AccountRepository;

class ClientController extends Controller
{
    private $clientRepository;
    private $userRepository;
    private $accountRepository;

    public function __construct(ClientRepository $clientRepository, UserRepository $userRepository, AccountRepository $accountRepository){
        
        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
    }

    public function index() {
        $clients = $this->clientRepository->getAll();
        return response()->json(['clients' => $clients],200);
    }


    public function update(Request $request, $id){

        // $client = Client::findOrFail($id);
        $client = $this->clientRepository->getClient($id);
        

        $user = $this->userRepository->update($client->user->id, $request->all());

        // return response()->json($this->clientRepository->getClient($id));

        $client = $this->clientRepository->update($client->id, $request->all());

        $account = null;
        foreach ($client->accounts as $compt ) {
            $account = $compt;
            break;
        }
        $account = $this->accountRepository->update($accounts->id, ['account_title'=> $user->name]);

        return response()->json($this->clientRepository->getClient($id));

        if ($user ) {
            #user collector
            if ($request['user_type'] == 1) {
                $collector = new Collector();
                $collector->user_id = $user->id;
                $collector->registration_number = $this->generateUniqueNumber();

                $collector->save();

                $collector->sectors()->attach([$request['sector']]);

                return response()->json([
                    'message' => 'Collecteur crée !',
                    'collectors' => $this->collectorRepository->getCollectors(),
                ], 200);

            }  #user client
            elseif ($request['user_type'] == 2) {
                try {
                    $client = new Client();
                    $client->user_id = $user->id;
                    $client->sector_id = $request['sector'];
                    $client->numero_comptoir = $request['numero_comptoir'];
                    $client->numero_registre_de_commerce = $request['numero_registre_de_commerce'];
                    $client->created_by = Auth::user()->id;

                    $client->save();
                } catch (\Throwable $th) {
                    //throw $th;
                    dd($th);
                    return response()->json([
                        'errors' => "erreur client",
                        'dd1'=> $request['numero_registre_de_commerce'],
                        'dd2'=> $request['numero_comptoir'],
                        'dd3'=> $request['sector'],
                        // 'dd4'=> Auth::user()->id,
                    ], 400);
                }
                if ($client) {
                    # création du compte
                    try {
                        $account = array();
                        $account['client_id'] = $client->id;
                        $account['account_title'] = $user->name;
                        $account['account_number'] = date('Y').substr(time(), -5).'-'.substr(time(), -2)+2;
                        $account['account_balance'] = 0;
                        $account['created_at'] = date("Y-n-j G:i:s");
                        $account['updated_at'] = date("Y-n-j G:i:s");

                        DB::table('accounts')->insert($account);
                        return response()->json([
                            'message' => 'Client crée !',
                            'clients' => $clients = $this->clientRepository->getAll(),
                        ], 200);
                    } catch (\Throwable $th) {
                        //throw $th;
                        return response()->json([
                            'errors' => "erreur compte",
                            'dd'=> $th,
                        ], 400);
                    }
                    
                } else {
                    return response()->json([
                        'errors' => "Echec de création du client"
                    ], 400);
                }

            } # user admin

            return response()->json([
                'message' => 'Administrateur crée !'
            ], 200);
        }
    }


    public function delete($id){

        try{
            
            $client= $this->clientRepository->destroy($id);

            return response()->json([
                'success'=>true, 
                'message'=>'client supprimé !',
                'clients' => $this->clientRepository->getAll(),
            ], 200);
        }catch(Exception $e){
            
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['success'=>false, 'message'=>'echec de la suppression'], 402);

        }
    }

    public function restore($id){

        try{

            $client= $this->clientRepository->restore($id);
            return response()->json(['success'=>true, 'message'=>$client], 200);

        }catch(Exception $e){
            
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['success'=>false, 'message'=>'echec de la suppression de ce client'], 402);

        }
    }

    public function restoreAll(){
        
        try{
            $clients = $this->clientRepository->restoreAll();
            return response()->json(['succes'=>true, 'message'=>$client], 200);

        }catch(Exception $e){
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['succes'=>false, 'message'=>'echec de la restauration'], 402);
        }
        
    }

    public function completelyDelete($id){
        
        try{
            $this->clientRepository->completelyDelete();
            return response()->json(['succes'=>true, 'message'=>'suppression définitivie réussie'], 200);

        }catch(Exception $e){
            return response()->json(['succes'=>false, 'message'=>'echec de la suppression définitive'], 402);
        }
    }

}
