<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends ResourceRepository {

    /**
     * @param Role $account
     */
    public function __construct(Role $account) {
        $this->model = $account;
    }

    public function getAll() {
        return $this->model->with('users')->get();
    }

    public function getRole($role='root') {
        return $this->model
            ->where('name', $role)
            ->first();
    }
}