<?php

namespace App\Http\Controllers;

use DB;
use App\Models\User;
use App\Models\Client;
use App\Models\Collector;
use App\Mail\MessageGoogle;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Repositories\ClientRepository;
use App\Http\Validation\UserValidation;
use App\Repositories\CollectorRepository;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    private $userRepository;
    private $collectorRepository;

    public function __construct(UserRepository $userRepository, CollectorRepository $collectorRepository, ClientRepository $clientRepository)
    {
        $this->userRepository = $userRepository;
        $this->collectorRepository = $collectorRepository;
        $this->clientRepository = $clientRepository;
    }

    public function index(){
        $users = $this->userRepository->getPaginate(10);
        return response()->json($users);
    }

    public function store(Request $request, UserValidation $userValidation) {
        // return response()->json([
        //     'dd4'=> $request->file('profil_image'),
        //     'dd2'=> $request->all(),
        //     // 'dd1'=> type($request->avatar2),
        // ], 200);
        $validator = Validator::make($request->all(), $userValidation->rules(), $userValidation->message());
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {

            $request['password'] = Hash::make('2s@Kollect');

            #Controle de l'authentification de l'utilisateur
            if (Auth::user()) {
                $authUser = Auth::user();
            } else {
                if (auth::user()) {
                    $authUser = auth::user();
                } else {
                    return response()->json([
                        'errors' => "Echec de création (Problème  d'authentification)"
                    ], 400);
                }
            }

            $user = $this->userRepository->store($request->all());

            if ($user ) {
                $user->roles()->attach([$request['role_id']]);
                #user collector
                if ($request['user_type'] == 1) {
                    $collector = new Collector();
                    $collector->user_id = $user->id;
                    $collector->registration_number = $this->generateUniqueNumber();

                    $collector->save();

                    $collector->sectors()->attach([$request['sector']]);

                    if ($this->sendMailNotification($request) != 0) {
                        $errorMail = 'Echec de notification mail';
                    }

                    return response()->json([
                        'message' => 'Collecteur crée !',
                        'errorMail' => isset($errorMail)? $errorMail : '',
                        'collectors' => $this->collectorRepository->getCollectors(),
                    ], 200);

                }  #user client
                else if ($request['user_type'] == 2) {
                    try {
                        $client = new Client();
                        $client->user_id = $user->id;
                        $client->sector_id = $request['sector'];
                        $client->numero_comptoir = $request['numero_comptoir'];
                        $client->numero_registre_de_commerce = $request['numero_registre_de_commerce'];
                        $client->created_by = $authUser->id;

                        $client->save();
                    } catch (Exception $th) {
                        //throw $th;
                        // dd($th);
                        return response()->json([
                            'err' => "erreur client",
                            'dd1'=> $request['numero_registre_de_commerce'],
                            'dd2'=> $request['numero_comptoir'],
                            'dd3'=> $request['sector'],
                            'dd4'=> $th
                        ], 400);
                    }
                    if ($client) {
                        # création du compte
                        try {
                            $account = array();
                            $account['client_id'] = $client->id;
                            $account['account_title'] = $user->name;
                            $account['account_number'] = date('Y').substr(time(), -5).'-'.substr(time(), -2)+2;
                            $account['account_balance'] = 0;
                            $account['created_at'] = date("Y-n-j G:i:s");
                            $account['updated_at'] = date("Y-n-j G:i:s");

                            DB::table('accounts')->insert($account);
                            return response()->json([
                                'message' => 'Client crée !',
                                'clients' => $clients = $this->clientRepository->getAll(),
                            ], 200);
                        } catch (\Throwable $th) {
                            //throw $th;
                            return response()->json([
                                'errors' => "erreur compte",
                                'dd'=> $th,
                            ], 400);
                        }

                    } else {
                        return response()->json([
                            'errors' => "Echec de création du client"
                        ], 400);
                    }

                } # user admin

                return response()->json([
                    'message' => 'Administrateur crée !'
                ], 200);
            }

            return response()->json([
                'errors' => "Echec de création de l'utilisateur"
            ], 400);
        }
    }


    public function generateUniqueNumber()
    {
        do {
            $code = Str::random(5);
        } while (Collector::where("registration_number", "=", $code)->first());

        return $code;
    }

    public function update(Request $request,UserValidation $userValidation,$id){

        return response()->json(['result' => 'Fonctionnalité non disponible']);
        $validator = Validator::make($request->all(), $userValidation->rules(), $userValidation->message());

         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 400);
         } else {

            $request->password = Hash::make($request->password);
            $inputs [] = $request->all();

            foreach ($inputs as $input ) {
                $input['password'] = Hash::make($request->password);
            }

            $user = $this->userRepository->update($id,$input);

            if ($user ) {
                #user collector
                if ($request['user_type'] == 1) {
                    $collector = new Collector();
                    $collector->user_id = $user->id;
                    $collector->registration_number = $this->generateUniqueNumber();

                    $collector->save();

                    $collector->sectors()->sync([$request['sector']]);

                    return response()->json([
                        'message' => 'Collecteur crée !'
                    ], 200);

                }  #user client
                elseif ($request['user_type'] == 2) {
                    $client = new Client();
                    $client->user_id = $user->id;
                    $client->sector_id = $request['sector'];
                    $client->numero_comptoir = $request['num_comptoir'];
                    $client->numero_registre_de_commerce = $request['registre_commerce'];
                    $client->created_by = auth::user()->id;  # A remplacer par Auth::user()->id;

                    $client->save();
                    if ($client) {
                        # création du compte
                        $account = array();
                        $account['client_id'] = $client->id;
                        $account['account_title'] = $user->name;
                        $account['account_number'] = date('Y').substr(time(), -5).'-'.substr(time(), -2)+2;
                        $account['account_balance'] = 0;
                        $account['created_at'] = date("Y-n-j G:i:s");
                        $account['updated_at'] = date("Y-n-j G:i:s");

                        DB::table('accounts')->insert($account);
                        return response()->json([
                            'message' => 'Client crée !'
                        ], 200);
                    } else {
                        return response()->json([
                            'errors' => "Echec de création du client"
                        ], 400);
                    }

                } # user admin

                return response()->json([
                    'message' => 'Administrateur crée !'
                ], 200);
            }

            return response()->json([
                'errors' => "Echec de création de l'utilisateur"
            ], 400);

        }
    }

    public function sendMailNotification(Request $request) {

        #1. Validation de la requête
		// $this->validate($request, [ 'message' => 'bail|required' ]);

		#2. Récupération des utilisateurs
		// $users = User::all();

		#3. Envoi du mail
		return Mail::to($request->email)->bcc("grantshell0@gmail.com")
						->queue(new MessageGoogle($request->all()));

        // toast("Un mail a été envoyé à l'utilisateur crée !");

		// return redirect('/user.index');
    }
}
