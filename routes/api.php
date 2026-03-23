<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlvaraController;
use App\Http\Controllers\Api\EmpresaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Todas as rotas de recursos protegidas por Sanctum
// Prefixo 'api.' nos nomes para não colidir com as rotas web (route('empresas.index') vs route('api.empresas.index'))
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::apiResource('alvaras', AlvaraController::class);
    Route::apiResource('empresas', EmpresaController::class);
});
