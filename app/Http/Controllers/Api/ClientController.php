<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Repositories\ClientRepository;

class ClientController extends Controller
{
    private $clientRepository;
    private $userRepository;

    public function __construct(ClientRepository $clientRepository, UserRepository $userRepository){
        
        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
    }

    public function index() {
        $clients = $this->clientRepository->getAll();
        return response()->json(['clients' => $clients],200);
    }

    public function delete($id){

        try{
            
            $client= $this->clientRepository->destroy($id);

            return response()->json(['succes'=>true, 'message'=>$client], 200);
        }catch(Exception $e){
            
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['succes'=>false, 'message'=>'echec de la suppression'], 402);

        }
    }

    public function restore($id){

        try{

            $client= $this->clientRepository->restore($id);
            return response()->json(['succes'=>true, 'message'=>$client], 200);

        }catch(Exception $e){
            
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['succes'=>false, 'message'=>'echec de la suppression de ce client'], 402);

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
