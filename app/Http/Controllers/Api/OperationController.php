<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ClientRepository;
use App\Repositories\SectorRepository;
use App\Repositories\AccountRepository;
use App\Repositories\CollectorRepository;
use App\Repositories\OperationRepository;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\OperationValidation;

class OperationController extends Controller
{
    private $operationRepository;
    private $accountRepository;
    private $collectorRepository;
    private $sectorRepository;

    public function __construct(OperationRepository $operationRepository, 
        AccountRepository $accountRepository, ClientRepository $clientRepository, 
        CollectorRepository $collectorRepository, SectorRepository $sectorRepository) {
        $this->operationRepository = $operationRepository;
        $this->accountRepository = $accountRepository;
        $this->clientRepository = $clientRepository;
        $this->collectorRepository = $collectorRepository;
        $this->sectorRepository = $sectorRepository;
    }

    public function index() {
        return response()->json([
            'clients' => $this->clientRepository->getAll(),
            'operations' => $this->operationRepository->getAll(),
            'sectors' => $this->sectorRepository->getAll(),
        ], 200);
    }


    public function store(Request $request, OperationValidation $operationValidation){
        $validator = Validator::make($request->all(), $operationValidation->rules(), $operationValidation->message());

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()],400);

        } else {
            $account = $this->accountRepository->getById($request['account_id']);
            #Test sur le solde en compte avant débit
            if ($request['type'] == -1 && $account->account_balance <= $request['amount']) {

                return response()->json(['errors' =>"Oups! Solde insuffisant"], 400);
            }

            # Remplacer l'id du user de la requête par son id de collecteur (car c'est l'id du user qui est envoyé)
            $collector = $this->collectorRepository->getCollectorByUserId($request->collector_id);
            // dd($collector);
            // return response()->json(['errors' =>$collector], 400);
            $request['collector_id'] = $collector->id;


           $operation = $this->operationRepository->store($request->all());

           if ($operation) {
                
                if ($request['type'] == 1) {
                    
                    #Incrémenter le solde du compte
                    $account->account_balance += $request['amount'];

                    $this->accountRepository->update($request['account_id'], $account->toArray());

                    return response()->json([
                        'message' =>"Versement éffectué !",
                        // 'clients' => $this->clientRepository->getAll(),
                        'clients' => $this->clientRepository->getClientSector($request->sector_id),
                    ], 200);
                 } else {
                    #Débiter le compte
                    $account->account_balance -= $request['amount'];

                    $this->accountRepository->update($request['account_id'], $account->toArray());

                     return response()->json([
                        'message' =>"Retrait éffectué !",
                        // 'clients' => $this->clientRepository->getAll(),
                        'clients' => $this->clientRepository->getClientSector($request->sector_id),
                    ], 200);
                 }
           }

           return response()->json(['errors' =>"Echec de l'opération"],400);

        }
    }
}
