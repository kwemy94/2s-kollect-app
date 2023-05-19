<?php

namespace App\Http\Controllers\Api;

use App\Http\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    private $clientRepository;

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

        toggleDatabase();
        
        return response()->json([
            'clients' => $this->clientRepository->getAll(),
            'operations' => $this->operationRepository->getAll(),
            'sectors' => $this->sectorRepository->getAll(),
        ], 200);
    }


    public function store(Request $request, OperationValidation $operationValidation)
    {
        toggleDatabase();
        $validator = Validator::make($request->all(), $operationValidation->rules(), $operationValidation->message());

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()],400);

        } else {
            $message = null;
            $error = false;
            try {
                DB::transaction(function() use($request, $error, $message) {
                    
                    $account = $this->accountRepository->getById($request['account_id']);

                    #Test sur le solde en compte avant débit
                    if (($request['type'] == -1 || $request['type'] == 0) && $account->account_balance <= $request['amount']) {

                        return response()->json(['errors' =>"Oups! Solde insuffisant"], 400);
                    }

                    # Remplacer l'id du user de la requête par son id de collecteur (car c'est l'id du user qui est envoyé)
                    # $collector = $this->collectorRepository->getCollectorByUserId($request->collector_id);
                    
                    # $request['collector_id'] = $collector->id;

                    #Reconduction montant
                    if($request['type'] == 0){
                        
                        $this->operationRepository->store($request->all());
                        // $message = "Reconduction éffectué !";
                        return response()->json([
                            'message' =>"Reconduction éffectué !",
                            'clients' => $this->clientRepository->getClientSector($request->sector_id),
                        ], 200);
                    }
                    
                    $this->operationRepository->store($request->all());

                    if ($request['type'] == 1) {
                    
                        #Incrémenter le solde du compte
                        $account->account_balance += $request['amount'];
    
                        $this->accountRepository->update($request['account_id'], $account->toArray());
    
                        return response()->json([
                            'message' =>"Versement éffectué !",
                            'clients' => $this->clientRepository->getClientSector($request->sector_id),
                        ], 200);
                    } else {
                        if ($request['type'] == -1) {
                            #Débiter le compte
                            $account->account_balance -= $request['amount'];
    
                            $this->accountRepository->update($request['account_id'], $account->toArray());
    
                            return response()->json([
                                'message' =>"Retrait éffectué !",
                                'clients' => $this->clientRepository->getClientSector($request->sector_id),
                            ], 200);
                        } else {
                            return response()->json([
                                'message' =>"Echec!! Type non reconnu",
                                'error' => true,
                            ], 400);
                        }
                    }
                });
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'errors' =>"Echec de l'opération",
                    'msg' => $th->getMessage(),
                ],400);
            }

            return response()->json([
                'message' =>$message,
                'clients' => $this->clientRepository->getClientSector($request->sector_id),
            ], 200);

        }
    }

    public function statistic() 
    {
        toggleDatabase();
        return response()->json([
            'secteurs' => $this->sectorRepository->getAll(),
            'clients' => $this->clientRepository->getAll(),
            'operations' => $this->operationRepository->getAll(),
        ]);
    }
}
