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
}