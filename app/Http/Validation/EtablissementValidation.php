<?php
namespace App\Http\Validation;

class EtablissementValidation {

    public function rules() {
      return   [
            'ets_name' => 'required|min:3',
            'ets_email' => 'required|email',
        ];
    }


    public function message() {
        return [
            'ets_name.required' => 'le nom est requis',
            'ets_name.min' => 'le nom doit avoir au moins 3 caractères',
            'ets_email.required' => 'Adresse email obligatoire',
            'ets_email.email' => 'Mauvaise adresse email',
        ];
    }
}
