<?php
namespace App\Http\Validation;

class EtablissementValidation {

    public function rules() {
      return   [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ];
    }


    public function message() {
        return [
            'name.required' => 'le nom est requis',
            'name.min' => 'le nom doit avoir au moins 3 caractères',
            'email.required' => 'Adresse email obligatoire',
            'email.email' => 'Mauvaise adresse email',
        ];
    }
}
