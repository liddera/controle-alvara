<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\OwnerWhatsAppInstanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OwnerWhatsAppConnectionController extends Controller
{
    public function __construct(private OwnerWhatsAppInstanceService $service) {}

    public function connect(Request $request): RedirectResponse
    {
        $ownerId = $request->user()->owner_id ?: $request->user()->id;

        try {
            $this->service->requestConnectionForOwner($ownerId);
            $this->service->refreshConnectionStateForOwner($ownerId);
        } catch (\Throwable $exception) {
            Log::error('Falha ao iniciar conexao WhatsApp.', [
                'owner_id' => $ownerId,
                'base_url' => config('services.whatsapp_gateway.base_url'),
                'provider' => config('services.whatsapp_gateway.provider'),
                'has_api_key' => filled(config('services.whatsapp_gateway.api_key')),
                'error' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]);

            $message = 'Falha ao iniciar conexao com WhatsApp. Verifique as configuracoes do gateway.';

            if (config('app.debug')) {
                $message .= ' (debug: '.$exception->getMessage().')';
            }

            return back()->with('error', $message);
        }

        return back()->with('success', 'Conexao com WhatsApp iniciada. Escaneie o QR code para concluir.');
    }

    public function refresh(Request $request)
    {
        $ownerId = $request->user()->owner_id ?: $request->user()->id;

        try {
            $this->service->refreshConnectionStateForOwner($ownerId);
        } catch (\Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => $this->service->getConnectionStatus($ownerId),
                    'error' => 'Falha ao atualizar status do WhatsApp.',
                ], 500);
            }

            return back()->with('error', 'Falha ao atualizar status do WhatsApp.');
        }

        if ($request->expectsJson()) {
            $instance = $this->service->findForOwner($ownerId);

            return response()->json([
                'status' => $this->service->getConnectionStatus($ownerId),
                'connected_at' => $instance?->connected_at,
                'last_webhook_at' => $instance?->last_webhook_at,
                'qr_base64' => $instance?->last_qr_code_base64,
                'qr_payload' => $instance?->last_qr_code_payload,
                'pairing_code' => $instance?->last_pairing_code,
            ]);
        }

        return back();
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $ownerId = $request->user()->owner_id ?: $request->user()->id;

        try {
            $this->service->disconnectOwner($ownerId);
        } catch (\Throwable $exception) {
            return back()->with('error', 'Falha ao desconectar WhatsApp.');
        }

        return back()->with('success', 'WhatsApp desconectado.');
    }
}
