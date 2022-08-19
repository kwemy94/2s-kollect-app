<?php

namespace App\Repositories;

use App\Models\Account;

class AccountRepository extends ResourceRepository {

    /**
     * @param Account $account
     */
    public function __construct(Account $account) {
        $this->model = $account;
    }
}