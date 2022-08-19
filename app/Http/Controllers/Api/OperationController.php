<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Repositories\OperationRepository;
use App\Repositories\AccountRepository;
use App\Http\Validation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\OperationValidation;

class OperationController extends Controller
{
    private $operationRepository;
    private $accountRepository;

    public function __construct(OperationRepository $operationRepository, AccountRepository $accountRepository) {
        $this->operationRepository = $operationRepository;
        $this->accountRepository = $accountRepository;
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

           $operation = $this->operationRepository->store($request->all());

           if ($operation) {
                
                if ($request['type'] == 1) {
                    
                    #Incrémenter le solde du compte
                    $account->account_balance += $request['amount'];

                    $this->accountRepository->update($request['account_id'], $account->toArray());

                    return response()->json(['message' =>"Versement éffectué !"], 200);
                 } else {
                    #Débiter le compte
                    $account->account_balance -= $request['amount'];

                    $this->accountRepository->update($request['account_id'], $account->toArray());

                     return response()->json(['message' =>"Retrait éffectué !"], 200);
                 }
           }

           return response()->json(['errors' =>"Echec de l'opération"],400);

        }
    }
}
