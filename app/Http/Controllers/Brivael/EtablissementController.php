<?php

namespace App\Http\Controllers\Brivael;

use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\EtablissementRepository;
use App\Http\Validation\EtablissementValidation;
use PHPUnit\Util\Json;

class EtablissementController extends Controller
{
    protected $etablissementRepository ;

    public function __construct(EtablissementRepository $etablissementRepository){
        $this->etablissementRepository = $etablissementRepository;
    }

    public function index(){
        /*
        * Retourner les informations permettant de spécifier l'établissent
        * (logo, nom établissent)
        */
    }

    public function store(Request  $request, EtablissementValidation $etablissementValidation) {
        
        $validator = Validator::make($request->all(), $etablissementValidation->rules(), $etablissementValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $uploadProfil = $request->file('logo');

        try {
            $database = null;
           $transaction = DB::transaction(function () use($request, &$database, $uploadProfil){
                if ($uploadProfil) {
                    $filename = Str::uuid() . '.' . $uploadProfil->getClientOriginalExtension();
                    Storage::disk('public')->putFileAs('etablissement/logo/', $uploadProfil, $filename);
        
                    $request['logo'] = $filename;
                }


                $database = '2s_'.$request->name.'_db';

                $data =  array("db" => array('database'=>$database,'username'=>'root','password'=>''),'momo'=> '{}');

                $request['settings'] = $data;
                
               $this->etablissementRepository->store($request->post());


            });

            if (is_null($transaction)) {
                Artisan::call('db:create', ['name'=> $database]);
                
                # Exécution des migrations dans les bases de données nouvellement crées
                Artisan::call('update:backend_db', ['path'=> 'backend_db']);

                Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
            }
            
            
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=> 'erreur survenue '.$th->getMessage()]);
        }

        #changement de statut (passer de 2 =>bd non créé à 1 =>  bd cré et migré)
        $etab = $this->etablissementRepository->lastField();
        $etab->status = 1;
        $this->etablissementRepository->update($etab->id, ['status'=>1]);

        return response()->json(['success' =>'Etablissement crée'], 200);
    }
}
