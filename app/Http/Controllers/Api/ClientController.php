<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use PDF;
use Exception;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Repositories\ClientRepository;
use App\Repositories\SectorRepository;
use App\Http\Validation\UserValidation;
use App\Repositories\AccountRepository;
use Illuminate\Support\Facades\Storage;
use App\Repositories\CollectorRepository;
use App\Repositories\OperationRepository;
use Illuminate\Support\Facades\Validator;

// use Barryvdh\DomPDF\PDF;

class ClientController extends Controller
{
    private $clientRepository;
    private $userRepository;
    private $accountRepository;
    private $collectorRepository;
    private $sectorRepository;
    private $operationRepository;

    public function __construct(
        ClientRepository $clientRepository, CollectorRepository $collectorRepository,
        UserRepository $userRepository, AccountRepository $accountRepository,
        SectorRepository $sectorRepository, OperationRepository $operationRepository
    )
    {

        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
        $this->collectorRepository = $collectorRepository;
        $this->sectorRepository = $sectorRepository;
        $this->operationRepository = $operationRepository;
    }

    public function index()
    {
        $clients = $this->clientRepository->getClientSector(null);
        return response()->json(['clients' => $clients], 200);
    }


    public function store(Request $request, UserValidation $userValidation)
    {
        $validator = Validator::make($request->all(), $userValidation->rules(), $userValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $uploadProfil = $request->file('profil');

        if ($uploadProfil) {
            $filename = Str::uuid() . '.' . $uploadProfil->getClientOriginalExtension();

        } else {
            $filename = null;
        }

        #Création password
        $request['password'] = Hash::make('2s@Client');
        $error = false;

        DB::transaction(function () use ($filename, $error, $uploadProfil, $request) {
            try {
                if (!is_null($filename)) {
                    Storage::disk('public')->putFileAs('uploadProfil/', $uploadProfil, $filename);
                    $request['avatar'] = $filename;
                }

                $user = $this->userRepository->store($request->all());

                $client = new Client();
                $client->user_id = $user->id;
                $client->sector_id = $request['sector'];
                $client->numero_comptoir = $request['numero_comptoir'];
                $client->numero_registre_de_commerce = $request['numero_registre_de_commerce'];
                $client->created_by = Auth::user()->id;

                $client->save();
            } catch (Exception $th) {
                //throw $th;
                // dd($th);
                // return response()->json([
                //     'err' => "erreur client",
                //     'dd1' => $request['numero_registre_de_commerce'],
                //     'dd2' => $request['numero_comptoir'],
                //     'dd3' => $request['sector'],
                //     'dd4' => $th->getMessage(),
                // ], 400);
                $error = $th->getMessage();
            }

            try {
                $account = array();
                $account['client_id'] = $client->id;
                $account['account_title'] = $user->name;
                $account['account_number'] = date('Y') . substr(time(), -5) . '-' . substr(time(), -2) + 2;
                $account['account_balance'] = 0;
                $account['created_at'] = date("Y-n-j G:i:s");
                $account['updated_at'] = date("Y-n-j G:i:s");

                DB::table('accounts')->insert($account);
                
            } catch (\Throwable $th) {
                //throw $th;
                // return response()->json([
                //     'errors' => "erreur compte",
                //     'dd' => $th,
                // ], 400);
                $error = $th->getMessage();
            }
        });

        if ($error != false) {
            return response()->json([
                'errors' => "Erreur survenue",
                'dd' => $error,
            ]);
        }

        return response()->json([
            'message' => 'Client crée !',
            'clients' => $this->clientRepository->getClientSector($request->sector),
        ], 201);
    }

    public function clientParSecteur($phone)
    {
        // $clients = $this->clientRepository->getClientSector();
        $user = $this->userRepository->getByPhone($phone);
        if ($user->collectors) {
            $collectorId = $user->collectors[0]->id;
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Oups! Collecteur non existant',
            ], 400);
        }
        // return response()->json(['clients' => $collectorId],200);
        $collector = $this->collectorRepository->getCollector($collectorId);

        if ($collector->sectors) {
            $sector_id = $collector->sectors[0]->pivot->sector_id;
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Oups! Secteur non existant',
            ], 400);
        }

        $clients = $this->clientRepository->getClientSector($sector_id);
        return response()->json(['clients' => $clients], 200);
    }


    public function update(Request $request, $id)
    {


        try {
            $client = $this->clientRepository->getClient($id);

            $this->userRepository->update($client->user->id, $request->all());

            $this->clientRepository->update($client->id, $request->all());

            // return response()->json($this->clientRepository->getClient($id));

            $account = null;
            foreach ($client->accounts as $compt) {
                $account = $compt;
                break; # En supposant que l'utilisateur à 1 seul compte
            }

            $this->accountRepository->update($account->id, ['account_title' => $request->name]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => 'Oups! Echec de mise à jour des informations du client',
            ], 402);
        }

        return response()->json([
            'error' => false,
            'message' => 'Mise à jour effectuée !',
            'clients' => $this->clientRepository->getAll(),
        ], 200);


    }


    public function delete($id)
    {


        try {

            $client = $this->clientRepository->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'client supprimé !',
                'clients' => $this->clientRepository->getAll(),
            ], 200);
        } catch (Exception $e) {

            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['success' => false, 'message' => 'echec de la suppression'], 402);

        }
    }

    public function restore($id)
    {

        try {

            $client = $this->clientRepository->restore($id);
            return response()->json(['success' => true, 'message' => $client], 200);

        } catch (Exception $e) {

            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['success' => false, 'message' => 'echec de la suppression de ce client'], 402);

        }
    }

    public function restoreAll()
    {

        try {
            $clients = $this->clientRepository->restoreAll();
            return response()->json(['succes' => true, 'message' => $clients], 200);

        } catch (Exception $e) {
            throw new DeleteResourceFailedException(null, null, null, $e);
            return response()->json(['succes' => false, 'message' => 'echec de la restauration'], 402);
        }

    }

    public function completelyDelete($id)
    {
        return response()->json(Client::findOrFail($id));
        try {
            $this->clientRepository->completelyDelete();
            return response()->json(['succes' => true, 'message' => 'suppression définitivie réussie'], 200);

        } catch (Exception $e) {
            return response()->json(['succes' => false, 'message' => 'echec de la suppression définitive'], 402);
        }
    }

    public function clientDownload($sector_id = 36)
    {
        $clients = $this->clientRepository->getClientSector($sector_id);
        $sector = $this->sectorRepository->getById($sector_id);
        $data = [
            'title' => '2S Kollect App',
            'date' => date('m/d/Y'),
            'clients' => $clients,
            'sector' => $sector,
        ];

        $pdf = PDF::loadView('download.client_par_secteur', $data)->setPaper('a4', 'landscape')->setWarnings(false);

        // return $pdf->download('client.pdf');exit
        return $pdf->stream();
    }


    public function downloadCustomerHistory(Request $request, $id = 28)
    {
        $client = $this->clientRepository->getById($id);
        if ($client) {
            $histo = $this->operationRepository->getCustomerHisto($request->post(), $client->accounts[0]->id);

            $data = [
                'title' => '2S Kollect App',
                'date' => date('m/d/Y'),
                'client' => $client,
                'operations' => $histo,
                'info' => $request->post(),
            ];

            $pdf = PDF::loadView('download.historique-client', $data)->setPaper('a4', 'landscape')->setWarnings(false);

            return $pdf->stream();
        }
    }

}