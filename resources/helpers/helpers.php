<?php
use App\Models\Collector;
use App\Mail\MessageGoogle;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


if (!function_exists("generateUniqueNumber")) {
    function generateUniqueNumber()
    {
        do {
            $code = Str::random(5);
        } while (Collector::where("registration_number", "=", $code)->first());

        return $code;
    }
}

if (!function_exists("sendMailNotification")) {
    function sendMailNotification(Request $request)
    {

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


if (!function_exists('toggleDatabase')) {
    function toggleDatabase($isClientDatabase = true)
    {
        if ($isClientDatabase) {
            // $userRepository = new UserRepository(new User());

            $user = \Auth::user(); //
            // dd('test', $user);
            if ($user):
                // dd(session());
                // dd($user);
                $etablissement = \DB::table('etablissements')->where('id', $user->etablissement_id)->first();
                // dd($etablissement);
                $settings = json_decode($etablissement->settings);


                config()->set('database.connections.mobility', [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => $settings->db->database,
                    'username' => $settings->db->username,
                    'password' => $settings->db->password,
                    'unix_socket' => env('DB_SOCKET', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                    'modes' => [
                        //'ONLY_FULL_GROUP_BY', // Disable this to allow grouping by one column
                        'STRICT_TRANS_TABLES',
                        'NO_ZERO_IN_DATE',
                        'NO_ZERO_DATE',
                        'ERROR_FOR_DIVISION_BY_ZERO',
                        //'NO_AUTO_CREATE_USER', // This has been deprecated and will throw an error in mysql v8
                        'NO_ENGINE_SUBSTITUTION',
                    ],
                ]);
                \DB::purge('mobility');
                $connection = \DB::connection('mobility');
                Config::set('database.default', $connection->getName());
            else:
                dd('test2');
                $connection = null;
            endif;
        } else {

            $connection = \DB::connection('mysql');
            Config::set('database.default', 'mysql');

        }

        return $connection;
    }
}