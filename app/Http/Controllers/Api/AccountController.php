<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ClientRepository;
use App\Repositories\AccountRepository;
use App\Repositories\CollectorRepository;
use App\Repositories\OperationRepository;

class AccountController extends Controller
{

    public function __construct(OperationRepository $operationRepository, 
        AccountRepository $accountRepository, ClientRepository $clientRepository, CollectorRepository $collectorRepository) {
        $this->operationRepository = $operationRepository;
        $this->accountRepository = $accountRepository;
        $this->clientRepository = $clientRepository;
        $this->collectorRepository = $collectorRepository;
    }

    public function historique($client_id){

        try {
            $client = $this->accountRepository->getHistorique($client_id);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                'errors' => 'Oups! Erreur survenu',
            ], 403);
        }
        

        if (is_null($client)) {
            return response()->json([
                'errors' => 'Oups! Client non trouvé',
            ], 403);
        }

        return response()->json([
            'historique' => $client,
        ], 200);
    }
}
