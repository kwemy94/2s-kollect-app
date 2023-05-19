<?php
/**
 * Created by PhpStorm.
 * User: chopgwe
 * Date: 18/04/2017
 * Time: 14:25
 */

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserRepository extends ResourceRepository {

    /**
     * @param User $user
     */
    public function __construct(User $user) {
        $this->model = $user;
    }

    public function getCollectors() { # en cours de reflexion
        return  $this->model
          ->where('etablissement_id', Auth::user()->etablissement_id)
          ->where('sector_id', null)
          ->where('id','!=', Auth::user()->id)
          ->get();
      }

    public function getAll(){
        return $this->model->with('roles', 'collectors', 'clients')->get();
    }

    public function getBySector($sectorId){
        return $this->model->with('roles',)->where('sector_id', $sectorId)->first();
    }
    public function getById($id){
        return $this->model->with('roles')->where('id', $id)->first();
    }
}