<?php
namespace App\Http\Validation;

class OperationValidation {

    public function rules() {
      return   [
            'type' => 'required',   # 1 pour versement et -1 pour retrait
            'amount' => 'required|',
            'account_id' => 'required|integer',
            'collector_id' => 'required|integer'
        ];
    }


    public function message() {
        return [
            'type.required' => "Type de l'opération requis",
            'amount.required' => "Montant de l'opération",
            // 'amount.double' => 'Montant doit être un nombre',
        ];
    }
}