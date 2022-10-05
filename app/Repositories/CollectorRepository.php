<?php
/**
 * Created by PhpStorm.
 * User: chopgwe
 * Date: 18/04/2017
 * Time: 14:25
 */

namespace App\Repositories;

use App\Models\Collector;

class CollectorRepository extends ResourceRepository {

    /**
     * @param Collector $collector
     */
    public function __construct(Collector $user) {
        $this->model = $user;
    }

    public function getCollectors() {
        return $this->model
            ->with('user')
            ->Paginate(15);
    }
}