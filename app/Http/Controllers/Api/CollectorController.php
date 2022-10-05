<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Repositories\CollectorRepository;
use App\Http\Controllers\Controller;

class CollectorController extends Controller
{
    private $collectorRepository;
    private $userRepository;

    public function __construct(CollectorRepository $collectorRepository, UserRepository $userRepository){
        
        $this->collectorRepository = $collectorRepository;
        $this->userRepository = $userRepository;
    }

    public function index(){
        $clients = $this->collectorRepository->getCollectors();

        return response()->json([$clients]);
    }
}
