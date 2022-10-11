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

    public function getAll() {
        return $this->model->with('user', 'sector', 'accounts')->orderBy('id', 'DESC')->get();
    }

    public function getClient($id) {
        return $this->model->with('user', 'accounts', 'sector')->findOrfail($id);
    }

    public function restore($id){
        
        return $this->model->withTrashed()->find($id)->restore();
    }

    public function restoreAll(){
        
        return $this->model->onlyTrashed()->restore();
    }

    public function completelyDelete($id){

        return $this->model->find($id)->forceDelete();
    }

}