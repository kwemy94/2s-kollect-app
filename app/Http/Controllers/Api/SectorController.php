<?php

namespace App\Http\Controllers\Api;

use App\Repositories\UserRepository;
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
    private $userRepository;

    public function __construct(SectorRepository $sectorRepository, 
    ClientRepository $clientRepository, RoleRepository $roleRepository, UserRepository $userRepository){
        $this->sectorRepository = $sectorRepository;
        $this->clientRepository = $clientRepository;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
    }

    public function index(Request $request){
        // dd(is_null($request->bearerToken()));
        $collectors = $this->userRepository->getCollectors();
        toggleDatabase();
        return response()->json([
            'secteurs' => $this->sectorRepository->getAll(),
            'collectors' => $collectors,
            // 'roles' => $this->roleRepository->getAll(),
        ], 200);
    }


    public function store(Request $request, SectorValidation $sectorValidation) {
        // return response()->json(JWTAuth::user());
        toggleDatabase(true);

        $validator = Validator::make($request->all(), $sectorValidation->rules(), $sectorValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        } else {

            try {
               $sector = $this->sectorRepository->store($request->all());
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json(['errors' => $th->getMessage(), ]);
            }

            if (!is_null($sector)) {
                try {
                    toggleDatabase(false);
                    $this->userRepository->update($request->collector_id, ['sector_id'=> $sector->id]);
                } catch (\Throwable $th) {
                    //throw $th;
                    return response()->json(['errors' => $th->getMessage(), ]);
                }
            }

            toggleDatabase();
            
            return response()->json([
                'message' =>"Secteur créer",
                'secteurs' => $this->sectorRepository->getAll()
            ], 200);
        }

    }

    public function update(Request $request,$id){
        toggleDatabase();
        // $validator = Validator::make($request->all(), $sectorValidation->rules(), $sectorValidation->message());

        $inputs = $request->all();

        if($inputs){

            $sector = $this->sectorRepository->update($id,$inputs);

            if ($sector) { 
                // dd($sector);
                try {
                    toggleDatabase(false);
                    $this->userRepository->update($request->collector_id, ['sector_id'=> $id]);
                } catch (\Throwable $th) {
                    //throw $th;
                    return response()->json(['errors' => $th->getMessage(), ]);
                }
                toggleDatabase();
            }
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
        
        toggleDatabase();
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
