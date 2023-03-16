<?php

namespace App\Repositories;

use App\Models\Operation;

class OperationRepository extends ResourceRepository
{

    /**
     * @param Operation $operation
     */
    public function __construct(Operation $operation)
    {
        $this->model = $operation;
    }

    public function getAll()
    {

        # Ne pas changer l'ordre du listing (car l'impact est perceptible au listing bilan)
        return $this->model->with('accounts', 'collector')->orderBy('id', 'ASC')->get();
    }

    public function getCustomerHisto($req, $account_id)
    {
        return $this->model->with('accounts', 'collector')
            ->where('account_id', $account_id)
            // ->whereBetween('created_at', [$req->startDate, $req->endDate])
            ->whereBetween('created_at', ['2022-11-10', '2022-12-30'])
            ->orderBy('id','ASC' )
            ->get();
    }
}