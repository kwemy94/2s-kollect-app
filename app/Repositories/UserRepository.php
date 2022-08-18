<?php
/**
 * Created by PhpStorm.
 * User: chopgwe
 * Date: 18/04/2017
 * Time: 14:25
 */

namespace App\Repositories;

use App\Models\User;

class UserRepository extends ResourceRepository {

    /**
     * @param User $user
     */
    public function __construct(User $user) {
        $this->model = $user;
    }
}