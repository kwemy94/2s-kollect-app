<?php
namespace App\Http\Validation;

class SectorValidation {

    public function rules() {
      return   [
            'name' => 'required|unique:sectors',
            'locality' => 'required',
            'collector_id' => 'nullable',
        ];
    }



    public function message() {
        return [
            'name.required' => 'Nom du secteur requis',
            'name.unique' => 'Ce nom de secteur existe déjà !',
            'locality.required' => 'Spécifier la localité',
            // 'collector_id.required' => 'Définir le collecteur du secteur',
        ];
    }
}