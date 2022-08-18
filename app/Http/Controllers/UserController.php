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

            $request->password = Hash::make($request->password);
            $inputs [] = $request->all();
            
            foreach ($inputs as $input ) {
                $input['password'] = Hash::make($request->password);
            }

            $user = $this->userRepository->store($input);

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
                    # code...

                } # user admin
                else {
                    # code...

                }
            }
            return response()->json([
                'message' => "Echec de création de l'utilisateur"
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
