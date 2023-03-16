<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

use App\Repositories\RoleRepository;
use App\Repositories\ClientRepository;
use App\Repositories\SectorRepository;
use App\Http\Validation\SectorValidation;
use Illuminate\Support\Facades\Validator;

class SectorController extends Controller
{
    private $sectorRepository;
    private $clientRepository;
    private $roleRepository;

    public function __construct(SectorRepository $sectorRepository, 
    ClientRepository $clientRepository, RoleRepository $roleRepository){
        $this->sectorRepository = $sectorRepository;
        $this->clientRepository = $clientRepository;
        $this->roleRepository = $roleRepository;
    }

    public function index(){
        return response()->json([
            'secteurs' => $this->sectorRepository->getAll(),
            'roles' => $this->roleRepository->getAll(),
        ], 200);
    }


    public function store(Request $request, SectorValidation $sectorValidation) {
        // return response()->json(JWTAuth::user());

        $validator = Validator::make($request->all(), $sectorValidation->rules(), $sectorValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {
            $this->sectorRepository->store($request->all());
            return response()->json([
                'message' =>"Secteur créer",
                'secteurs' => $this->sectorRepository->getAll()
            ], 200);
        }

    }

    public function update(Request $request,$id,SectorValidation $sectorValidation){
        $validator = Validator::make($request->all(), $sectorValidation->rules(), $sectorValidation->message());

        $inputs = $request->all();

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {
            if($inputs){

                $this->sectorRepository->update($id,$inputs);
                return response()->json([
                    'message' =>"Secteur mis à jour",
                    'secteurs' => $this->sectorRepository->getAll(),
                ], 200);
            }
          else{
            return response()->json([
                'message' =>"Echec de mise à jour du secteur",
            ], 402);
          }
        }

    }

    public function show($id){
        $sector = $this->sectorRepository->getSector($id);

        if ($sector) {
            return response()->json([
                'secteur' => $sector,
                'clients' => $this->clientRepository->getClientSector($id),
                'error' => false,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Oups! Secteur introuvable',
                'error' => true,
            ], 400);
        }
        
        
    }

    public function destroy($id) {
        
        try {
            $this->sectorRepository->destroy($id);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message' => 'Oups! Impossible de supprimer le secteur'],403);
        }

        return response()->json([
            'secteurs' => $this->sectorRepository->getAll(),
            'message' => "Suppression effectuée"
        ],200);

        
    }
}
