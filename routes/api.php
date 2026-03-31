<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlvaraController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\EmailWebhookController;
use App\Http\Controllers\WhatsAppGatewayWebhookController;

// Rotas públicas (estritamente para autenticação se necessário via token)
Route::post('/login', [ApiAuthController::class, 'login']);

// Webhooks do gateway WhatsApp (publico, protegido por secret em header)
Route::post('/webhooks/whatsapp-gateway/{event?}', WhatsAppGatewayWebhookController::class)
    ->name('webhooks.whatsapp-gateway');

// Webhook do provedor de email (publico, protegido por secret em header)
Route::post('/webhooks/email-provider/transactional', EmailWebhookController::class)
    ->name('webhooks.email-provider');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Todas as rotas de recursos protegidas por Sanctum
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::apiResource('alvaras', AlvaraController::class);
    Route::apiResource('empresas', EmpresaController::class);
});
