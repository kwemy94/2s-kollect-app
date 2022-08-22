<?php

use App\Http\Controllers\Api\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\OperationController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/client', [UserController::class, 'index']);
Route::post('/client-store', [UserController::class, 'store']);
Route::post('/client-update-{id}',[UserController::class, 'update']);

Route::get('/sector', [SectorController::class, 'index']);
Route::post('/sector-store', [SectorController::class, 'store']);
Route::post('/sector-update-{id}', [SectorController::class, 'update']);

Route::post('/operation-store', [OperationController::class, 'store']);

//Update Route
