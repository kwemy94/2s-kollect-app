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
            ->with( 'sectors')
            ->get();
    }

    public function getCollector($id) {
        return $this->model->with( 'sectors')->findOrFail($id);
    }


    public function getCollectorByUserId($user_id) {
        return $this->model->with('sectors', 'operations')->where('user_id', $user_id)->first();
    }
}