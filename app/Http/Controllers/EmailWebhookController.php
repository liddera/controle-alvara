<?php

namespace App\Http\Controllers;

use App\Services\Email\EmailWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailWebhookController extends Controller
{
    public function __construct(private EmailWebhookService $service) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->service->handle($request);

        return response()->json(['ok' => true]);
    }
}
