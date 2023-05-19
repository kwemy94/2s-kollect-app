<?php

namespace App\Repositories;

use App\Models\Backend\Account;

class AccountRepository extends ResourceRepository {

    /**
     * @param Account $account
     */
    public function __construct(Account $account) {
        $this->model = $account;
    }

    public function getHistorique($clientId) {
        return $this->model->with('client', 'operations')->where('client_id', $clientId)->first();
    }
}