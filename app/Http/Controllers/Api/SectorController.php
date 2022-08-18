<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Repositories\SectorRepository;
use App\Http\Controllers\Controller;
use App\Http\Validation\SectorValidation;

use Illuminate\Support\Facades\Validator;

class SectorController extends Controller
{
    private $sectorRepository;

    public function __construct(SectorRepository $sectorRepository){
        $this->sectorRepository = $sectorRepository;
    }

    public function index(){
        return response()->json([$this->sectorRepository->getPaginate(10)], 200);
    }


    public function store(Request $request, SectorValidation $sectorValidation) {

        $validator = Validator::make($request->all(), $sectorValidation->rules(), $sectorValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {
            $this->sectorRepository->store($request->all());
            return response()->json([
                'message' =>"Secteur créer",
            ], 200);
        }

    }
}
