<?php

namespace App\Http\Controllers\Api;

use PDF;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Backend\Client;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
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
        // $this->middleware('JWT');
        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
        $this->collectorRepository = $collectorRepository;
        $this->sectorRepository = $sectorRepository;
        $this->operationRepository = $operationRepository;
    }

    public function index()
    {
        toggleDatabase(true);
        $clients = $this->clientRepository->getClientSector();
        // $clients = [];
        return response()->json(['clients' => $clients], 200);
    }


    public function store(Request $request, UserValidation $userValidation)
    {
        $validator = Validator::make($request->all(), $userValidation->rules(), $userValidation->message());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        
        toggleDatabase();

        $uploadProfil = $request->file('profil');

        if ($uploadProfil) {
            $filename = Str::uuid() . '.' . $uploadProfil->getClientOriginalExtension();

        } else {
            $filename = null;
        }

        #Création password
        $request['password'] = Hash::make('2s@Client');
        $error = false;

        $inputs = $request->post();

        try {
            DB::transaction(function () use ($filename, $inputs, $error, $uploadProfil, $request) {
                if (!is_null($filename)) {
                    Storage::disk('public')->putFileAs('profil/clients/', $uploadProfil, $filename);
                    $inputs['avatar'] = $filename;
                }

                $client = $this->clientRepository->store($inputs);

                $account = array();
                $account['client_id'] = $client->id;
                $account['account_title'] = $client->name;
                $account['account_number'] = date('Y') . substr(time(), -5) . '-' . substr(time(), -2) + 2;
                $account['account_balance'] = 0;
                $account['created_at'] = date("Y-n-j G:i:s");
                $account['updated_at'] = date("Y-n-j G:i:s");

                DB::table('accounts')->insert($account);
            });
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

        if ($error != false) {
            return response()->json([
                'errors' => "Erreur survenue",
                'dd' => $error,
            ]);
        }

        return response()->json([
            'message' => 'Client crée !',
            'clients' => $this->clientRepository->getClientSector($request->sector_id),
        ], 201);
    }

    public function clientParSecteur($sectorId)
    {
        toggleDatabase();
        $clients = $this->clientRepository->getClientSector($sectorId);

        return response()->json(['clients' => $clients], 200);
    }


    public function update(Request $request, $id)
    {
        toggleDatabase();
        $inputs = $request->post();
        try {

            $client = $this->clientRepository->getClient($id);

            DB::transaction(function() use($inputs, $client, $request) {

                $uploadProfil = $request->file('profil');

                if ($uploadProfil) {
                    $filename = Str::uuid() . '.' . $uploadProfil->getClientOriginalExtension();

                    #Supprimer l'ancien fichier
                    if (!is_null($client->avatar) ) {
                        $path = public_path().'/storage/profil/clients/';
                        $file_old = $path.$client->avatar;
                        unlink($file_old);
                    }
                    Storage::disk('public')->putFileAs('profil/clients/', $uploadProfil, $filename);
                    $inputs['avatar'] = $filename;
                    
                } else {
                    $filename = null;
                }

                $this->clientRepository->update($client->id, $inputs);

                $account = null;
                foreach ($client->accounts as $compt) {
                    $account = $compt;
                    break; # En supposant que l'utilisateur à 1 seul compte
                }

                $this->accountRepository->update($account->id, ['account_title' => $inputs['name']]);
            });

        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'dd' => $th->getMessage(),
                'message' => 'Oups! Echec de mise à jour des informations du client',
            ], 402);
        }

        return response()->json([
            'error' => false,
            'message' => 'Mise à jour effectuée !',
            'clients' => $this->clientRepository->getClientSector($client->sector_id),
        ], 200);


    }


    public function destroy($id)
    {
        toggleDatabase();
        try {
            $info = $this->clientRepository->getById($id);

            $client = $this->clientRepository->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'client supprimé !',
                'clients' => $this->clientRepository->getClientSector($info->sector_id),
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

    public function clientDownload($sector_id)
    {
        toggleDatabase();
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