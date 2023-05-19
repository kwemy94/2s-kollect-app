<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Collector;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Http\Validation\UserValidation;
use Illuminate\Support\Facades\Storage;
use App\Repositories\CollectorRepository;
use Illuminate\Support\Facades\Validator;

class CollectorController extends Controller
{
    private $collectorRepository;
    private $userRepository;

    public function __construct(CollectorRepository $collectorRepository, UserRepository $userRepository)
    {
        // $this->middleware('JWT');
        $this->collectorRepository = $collectorRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        toggleDatabase(true);
        $clients = $this->collectorRepository->getCollectors();

        return response()->json(['collectors' => $clients], 200);
    }

    public function store(Request $request, UserValidation $userValidation)
    {

        toggleDatabase(true);
        $validator = Validator::make($request->all(), $userValidation->rules(), $userValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $uploadProfil = $request->file('profil');

        if ($uploadProfil) {
            $filename = Str::uuid() . '.' . $uploadProfil->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('uploadProfil/', $uploadProfil, $filename);

            $request['avatar'] = $filename;
        }

        #Création password
        $request['password'] = Hash::make('2s@Kollect');
        $error = false;

        DB::transaction(function () use ($error, $request) {
            try {
                $user = $this->userRepository->store($request->all());

                $user->roles()->attach([$request['role_id']]);

                $collector = new Collector();
                $collector->user_id = $user->id;
                $collector->registration_number = generateUniqueNumber();

                $collector->save();

                $collector->sectors()->attach([$request['sector']]);

                if (sendMailNotification($request) != 0) {
                    $errorMail = 'Echec de notification mail';
                }
            } catch (\Throwable $th) {
                //throw $th;
                $error = $th->getMessage();
            }


        });

        if ($error != false) {
            return response()->json([
                'errors' => $error
            ], 400);
        }

        return response()->json([
            'message' => 'Collecteur crée !',
            'errorMail' => isset($errorMail) ? $errorMail : '',
            'collectors' => $this->collectorRepository->getCollectors(),
        ], 200);

    }


    public function update(Request $request, $id)
    {

        toggleDatabase(true);
        try {
            $collector = $this->collectorRepository->getCollector($id);

            $this->userRepository->update($collector->user->id, $request->all());

            $collector->sectors()->detach();
            $collector->sectors()->attach([$request['sector']]);

        } catch (Exception $e) {
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json([
                'error' => true,
                'message' => 'Oups! Echec de mise à jour des informations du collecteur',
            ], 402);
        }

        return response()->json([
            'error' => false,
            'message' => 'Mise à jour effectuée !',
            'collectors' => $this->collectorRepository->getCollectors(),
        ], 200);
    }

    public function show($id)
    {
        $collector = $this->collectorRepository->getCollector($id);
        dd($collector);
        /*   if ($sector) {
        return response()->json([
        'secteur' => $sector,
        'clients' => $this->clientRepository->getClientSector($id),
        'error' => false,
        ], 200);
        } else {
        return response()->json([
        'message' => 'Oups! Secteur introuvable',
        'error' => true,
        ], 200);
        } */


    }
}