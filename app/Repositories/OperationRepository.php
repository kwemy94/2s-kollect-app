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
        return $this->model->with('accounts', 'collector')->orderBy('id', 'DESC')->get();
    }
}