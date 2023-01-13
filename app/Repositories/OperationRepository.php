<?php

namespace App\Repositories;

use App\Models\Operation;

class OperationRepository extends ResourceRepository {

    /**
     * @param Operation $operation
     */
    public function __construct(Operation $operation) {
        $this->model = $operation;
    }

    public function getAll() {

        # Ne pas changer l'ordre du listing (car l'impact est perceptible au listing bilan)
        return $this->model->with('accounts', 'collector')->orderBy('id', 'ASC')->get();
    }
}