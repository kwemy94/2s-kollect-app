<?php
namespace App\Http\Validation;

class UserValidation {

    public function rules() {
      return   [
            'name' => 'required|min:3',
            'sexe' => 'required',
            'phone' => 'required|integer',
            'email' => 'required|email|unique:users',
            'password' => 'nullable',
            'cni' => 'required|unique:users',
            'user_type' => 'required|integer',  # 0 pour admin, 1 pour collector, 2 pour client
            'sector' => '',
            'num_comptoir' => '',
            'registre_commerce' => ''
        ];
    }


    public function message() {
        return [
            'name.required' => 'le nom est requis',
            'name.min' => 'le nom doit avoir au moins 3 caractères',
            'sexe.required' => 'Spécification du sexe obligatoire',
            'phone.required' => 'Spécification du sexe obligatoire',
            'phone.integer' => 'Les chaines de caractères ne sont pas acceptées',
            'email.required' => 'Email requis',
            'email.email' => 'Email non valide !',
            'email.unique' => 'Email déjà utilisé',
            'password.required' => 'Password requis !',
            'cni.required' => 'CNI requis !',
            'cni.unique' => 'Numéro cni déjà utilisé !',
            'user_type.required' => 'Précisez le type d\'utilisateur (collecteur ou client)',
        ];
    }
}