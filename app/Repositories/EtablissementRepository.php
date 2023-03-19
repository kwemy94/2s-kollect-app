<?php

namespace App\Repositories;

use App\Models\Brivael\Etablissement;

class EtablissementRepository extends ResourceRepository {

    /**
     * @param Etablissement $collector
     */
    public function __construct(Etablissement $etablissement) {
        $this->model = $etablissement;
    }

    public function getAll($status = 2) {
        return $this->model
            ->where('status', $status)
            //->orderBy('created_at', 'asc')
            ->orderBy('ets_name', 'asc')
            ->get();
    }
    public function lastField() {
        toggleDatabase(false);
        return $this->model
            ->orderBy('ets_name', 'asc')
            ->where('status',2)
            ->latest()
            ->first();
    }

    public function findByDomain($domain, $status = 2) {
        return $this->model
            ->where('domain', '=', $domain)
            ->where('status', $status)
            ->first();
    }


    public function getOne($status = 1) {
        return $this->model
            ->where('status', $status)
            ->orderBy('ets_name', 'asc')
            ->first();
    }


    public function storeEts($request, $settings) {
        
        $etab = new Etablissement();
        $etab->ets_name = $request['ets_name'];
        $etab->ets_email = $request['ets_email'];
        $etab->status = 2;
        $etab->settings = json_encode($settings);
        $etab->save();

        return $etab;
    }

    
}