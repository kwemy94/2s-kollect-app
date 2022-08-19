<?php

namespace App\Repositories;

use App\Models\Client;

class ClientRepository extends ResourceRepository {

    /**
     * @param Client $client
     */
    public function __construct(Client $client) {
        $this->model = $client;
    }
}