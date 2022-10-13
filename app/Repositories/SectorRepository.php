<?php

namespace App\Repositories;

use App\Models\Sector;

class SectorRepository extends ResourceRepository {

    /**
     * @param Sector $sector
     */
    public function __construct(Sector $sector) {
        $this->model = $sector;
    }

    public function getAll(){
        return $this->model
            ->with('collectors', 'clients')
            ->orderBy('id', 'DESC')    
            ->get();
    }

    public function getSector($id) {
        return $this->model->with('clients', 'collectors')->findOrFail($id);
    }
}