<?php

use App\Http\Controllers\Api\V1\ClientesController;
use App\Http\Controllers\Api\V1\MotoristasController;
use App\Http\Controllers\Api\V1\VeiculosController;
use App\Http\Controllers\Api\V1\ViagensController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->name('api.v1.')->group(function () {
    // "viagens" -> "viagem": Str::singular() não reconhece o português e geraria
    // {viagen}, que não bate com o parâmetro $viagem do controller (binding falha).
    Route::apiResource('viagens', ViagensController::class)
        ->parameters(['viagens' => 'viagem'])
        ->only(['index', 'show']);
    Route::apiResource('motoristas', MotoristasController::class)->only(['index', 'show']);
    Route::apiResource('veiculos', VeiculosController::class)->only(['index', 'show']);
    Route::apiResource('clientes', ClientesController::class)->only(['index', 'show']);
});
