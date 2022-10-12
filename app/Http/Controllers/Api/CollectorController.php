<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Repositories\CollectorRepository;
use App\Http\Controllers\Controller;

class CollectorController extends Controller
{
    private $collectorRepository;
    private $userRepository;

    public function __construct(CollectorRepository $collectorRepository, UserRepository $userRepository){
        
        $this->collectorRepository = $collectorRepository;
        $this->userRepository = $userRepository;
    }

    public function index(){
        $clients = $this->collectorRepository->getCollectors();

        return response()->json(['collectors' => $clients], 200);
    }


    public function update(Request $request, $id) {

        try {
            $collector = $this->collectorRepository->getCollector($id);
        
            $this->userRepository->update($collector->user->id, $request->all());

            $collector->sectors()->detach();
            $collector->sectors()->attach([$request['sector']]);

        } catch (Exception $e) {
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json([
                'error' => true,
                'message' => 'Oups! Echec de mise à jour des informations du collecteur',
            ], 402);
        }
        
        return response()->json([
            'error' => false,
            'message' => 'Mise à jour effectuée !',
            'collectors'=> $this->collectorRepository->getCollectors(),
        ], 200);
    }
}
