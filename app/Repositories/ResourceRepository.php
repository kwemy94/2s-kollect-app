<?php
/**
 * Created by PhpStorm.
 * User: tgb17
 * Date: 09/04/17
 * Time: 16:59
 */

namespace App\Repositories;


abstract class ResourceRepository {

    protected $model;

    public function getPaginate($n=null) { 
        if($n){
            return $this->model->paginate($n);
        }
        return $this->model->get();
    }

    public function store($inputs) {
        return $this->model->create($inputs);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->model->where('id', $id)->first();
    }

    public function update($id, $inputs) {
        return $this->getById($id)->update($inputs);
    }

    public function destroy($id) {
        return $this->getById($id)->delete();
    }

}

