<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ClientRepository extends ResourceRepository {

    /**
     * @param Client $client
     */

     private $collectorRepository;
    public function __construct(Client $client, CollectorRepository $collectorRepository) {
        $this->model = $client;
        $this->collectorRepository = $collectorRepository;
    }

    public function getAll() {
        return $this->model->with('user', 'sector', 'accounts')->orderBy('id', 'DESC')->get();
    }

    public function getClient($id) {
        return $this->model->with('user', 'accounts', 'sector')->findOrfail($id);
    }

    public function restore($id){
        
        return $this->model->withTrashed()->find($id)->restore();
    }

    public function restoreAll(){
        
        return $this->model->onlyTrashed()->restore();
    }

    public function completelyDelete($id){

        return $this->model->find($id)->forceDelete();
    }

    public function getClientSector($sectorId=0) {
        // return response()->json(['result'=> Auth::user()->collectors, 'status'=>is_null($sectorId)],200);
        if($sectorId == 0){
            $collecteur = Auth::user();
            return response()->json(['result'=> $collecteur],200);
            // dd('icciii', $collecteur);
            // dd($this->collectorRepository ->getById($collecteur->id));
        }
        return $this->model->with('user', 'sector', 'accounts')->where('sector_id', $sectorId)->get();
    }

    
    public function getClientSector2($sectorId) {
        $user = $this->model->with('user', 'sector', 'accounts')->where('sector_id', $sectorId)->get();


        // $user = $this->userRepository->getByPhone($phone);
        if ($user->collectors) {
            $collectorId = $user->collectors[0]->id;
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Oups! Collecteur non existant',
            ], 400);
        }
        // return response()->json(['clients' => $collectorId],200);
        $collector = $this->collectorRepository->getCollector($collectorId);
        
        if ($collector->sectors) {
            $sector_id = $collector->sectors[0]->pivot->sector_id;
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Oups! Secteur non existant',
            ], 400);
        }
        
        // $clients = $this->clientRepository->getClientSector($sector_id);
        // return response()->json(['clients' => $clients],200);
    }

}