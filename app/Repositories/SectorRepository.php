<?php
/**
 * Created by PhpStorm.
 * User: chopgwe
 * Date: 18/04/2017
 * Time: 14:25
 */

namespace App\Repositories;

use App\Models\Sector;

class SectorRepository extends ResourceRepository {

    /**
     * @param User $user
     */
    public function __construct(Sector $user) {
        $this->model = $user;
    }
}