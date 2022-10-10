<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\CollectorController;
use App\Http\Controllers\Api\OperationController;
// use App\Http\Controllers\Api\ClientController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/client', [UserController::class, 'index']);
// Route::post('/client-store', [UserController::class, 'store']);
Route::post('/client-update-{id}',[UserController::class, 'update']);

// Route::get('/sector', [SectorController::class, 'index']);
// Route::post('/sector-store', [SectorController::class, 'store']);
// Route::post('/sector-update-{id}', [SectorController::class, 'update']);

Route::post('/operation-store', [OperationController::class, 'store']);




Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

    Route::get('/sector', [SectorController::class, 'index']);
    Route::post('/sector-store', [SectorController::class, 'store']);
    Route::post('/sector-update-{id}', [SectorController::class, 'update']);

    Route::post('/collectors', [CollectorController::class, 'index']);

    # user store
    Route::post('/user-store', [UserController::class, 'store']);


    # client zone
    Route::post('/client', [ClientController::class, 'index']);

});

//delete a specific client
Route::get('/client/delete/{id}', [ClientController::class, 'delete']);
//restore a specific client deleted
Route::get('/client/restore/{id}', [ClientController::class, 'restore']);
//restore all clients partiellement deleted
Route::get('/client/restore_all', [ClientController::class, 'restoreAll']);
//delete definitively a client in database
Route::get('/client/destroy/{id}', [ClientController::class, 'completelyDelete']);

//Update Route