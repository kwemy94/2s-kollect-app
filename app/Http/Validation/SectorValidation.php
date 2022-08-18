<?php
namespace App\Http\Validation;

class SectorValidation {

    public function rules() {
      return   [
            'name' => 'required|',
            'locality' => 'required',
        ];
    }


    public function message() {
        return [
            'name.required' => 'Nom du secteur requis',
            'locality.required' => 'Spécifier la localité',
        ];
    }
}