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

        
        try {
            $client = $this->clientRepository->getClient($id);
        
            $this->userRepository->update($client->user->id, $request->all());

            $this->clientRepository->update($client->id, $request->all());

            // return response()->json($this->clientRepository->getClient($id));

            $account = null;
            foreach ($client->accounts as $compt ) {
                $account = $compt;
                break; # En supposant que l'utilisateur à 1 seul compte
            }

            $this->accountRepository->update($account->id, ['account_title'=> $request->name]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => 'Oups! Echec de mise à jour des informations du client',
            ], 402);
        }

        return response()->json([
            'error' => false,
            'message' => 'Mise à jour effectuée !',
            'clients'=> $this->clientRepository->getAll(),
        ], 200);

       
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
