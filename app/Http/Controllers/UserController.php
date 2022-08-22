<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Models\Client;
use App\Models\Collector;
use Illuminate\Support\Facades\Validator;
use App\Http\Validation\UserValidation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;


class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(){
        $users = $this->userRepository->getPaginate(10);
        return response()->json($users);
    }

    public function store(Request $request, UserValidation $userValidation) {
        $validator = Validator::make($request->all(), $userValidation->rules(), $userValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {

            $request['password'] = Hash::make($request->password);
            
            $user = $this->userRepository->store($request->all());

            if ($user ) {
                #user collector
                if ($request['user_type'] == 1) {
                    $collector = new Collector();
                    $collector->user_id = $user->id;
                    $collector->registration_number = $this->generateUniqueNumber();

                    $collector->save();

                    $collector->sectors()->attach([$request['sector']]);

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
                    $client->created_by = 1;  # A remplacer par Auth::user()->id;

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


    public function generateUniqueNumber()
    {
        do {
            $code = Str::random(5);
        } while (Collector::where("registration_number", "=", $code)->first());
  
        return $code;
    }
}
