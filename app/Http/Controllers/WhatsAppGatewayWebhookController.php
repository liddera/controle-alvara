<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\WhatsAppWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppGatewayWebhookController extends Controller
{
    public function __construct(private WhatsAppWebhookService $service) {}

    public function __invoke(Request $request, ?string $event = null): JsonResponse
    {
        $this->service->handle($event, $request);

        return response()->json(['ok' => true]);
    }
}
