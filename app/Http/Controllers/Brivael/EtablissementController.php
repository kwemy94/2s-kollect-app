<?php

namespace App\Http\Controllers\Brivael;

use App\Jobs\SendMailJob;
use App\Repositories\RoleRepository;
use PHPUnit\Util\Json;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\EtablissementRepository;
use App\Http\Validation\EtablissementValidation;

class EtablissementController extends Controller
{
    protected $etablissementRepository ;
    protected $userRepository ;
    protected $roleRepository ;

    public function __construct(EtablissementRepository $etablissementRepository, UserRepository $userRepository, RoleRepository $roleRepository){
        $this->middleware('JWT', ['except' => ['store']]);

        $this->etablissementRepository = $etablissementRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function index(Request $request){
        /*
        * Retourner les informations permettant de spécifier l'établissent
        * (logo, nom établissent)
        */

        try {
            $user = JWTAuth::setToken($request->bearerToken())->toUser();

            $etablissement = $this->etablissementRepository->getById($user->etablissement_id);

        } catch (\Throwable $th) {

            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ]);
        }
        
        return response()->json([
            'error' => false,
            'etablissement' => $etablissement
        ], 200);
    }

    public function store(Request  $request, EtablissementValidation $etablissementValidation) {
        
        $validator = Validator::make($request->all(), $etablissementValidation->rules(), $etablissementValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $uploadProfil = $request->file('logo');

        try {
            $database = null;
            
            $admin_user['name'] = $request->name;
            $admin_user['phone'] = $request->phone;
            $admin_user['email'] = $request->email;
            $admin_user['password'] = Hash::make('2s@Kollect');
            

           $transaction = DB::transaction(function () use($request, &$admin_user, &$database, $uploadProfil){
                if ($uploadProfil) {
                    $filename = Str::uuid() . '.' . $uploadProfil->getClientOriginalExtension();
                    Storage::disk('public')->putFileAs('etablissement/logo/', $uploadProfil, $filename);
        
                    $request['logo'] = $filename;
                }


                $database = '2s_'.$request->name.'_db';

                $data =  array("db" => array('database'=>$database,'username'=>'root','password'=>''),'momo'=> '{}');

                // $etab['ets_name'] = $request->ets_name;
                // $etab['ets_email'] = $request->ets_email;
                // $etab['settings'] = $data;
                
               $etablissement = $this->etablissementRepository->storeEts($request, $data);
                // dd($etablissement);
               # Création de l'admin de l'établissement
                $admin_user['etablissement_id'] = $etablissement->id;
                $admin_user = $this->userRepository->store($admin_user);
                $role = $this->roleRepository->getRoot();
                $admin_user->roles()->attach([$role->id]);

            });

            #Mettre le contenu ci-dessous dans un job + envoi de mail
            if (is_null($transaction)) {
                Artisan::call('db:create', ['name'=> $database]);
                
                # Exécution des migrations dans les bases de données nouvellement crées
                Artisan::call('update:backend_db', ['path'=> 'backend_db']);

                Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

                // SendMailJob::dispatch($request);
                \sendMailNotification($request);
            }
            
            
        } catch (\Throwable $th) {
            //throw $th;
            // dd($admin_user);
            return response()->json(['error'=> 'erreur survenue '.$th->getMessage()]);
        }

        #changement de statut (passer de 2 =>bd non créé à 1 =>  bd cré et migré)
        $etab = $this->etablissementRepository->lastField();
        $etab->status = 1;
        $this->etablissementRepository->update($etab->id, ['status'=>1]);

        return response()->json(['success' =>'Etablissement crée'], 200);
    }
}
