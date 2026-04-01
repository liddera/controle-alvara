<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\WhatsAppWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayWebhookController extends Controller
{
    public function __construct(private WhatsAppWebhookService $service) {}

    public function __invoke(Request $request, ?string $event = null): JsonResponse
    {
        Log::info('Webhook WhatsApp payload bruto.', [
            'event' => $event,
            'body' => $request->getContent(),
            'json' => $request->all(),
        ]);

        $this->service->handle($event, $request);

        return response()->json(['ok' => true]);
    }
}
