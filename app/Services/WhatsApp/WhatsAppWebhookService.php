<?php

namespace App\Services\WhatsApp;

use App\Models\DocumentDispatchEvent;
use App\Models\DocumentDispatchMessage;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppOutboxMessage;
use App\Services\Dispatch\DocumentDispatchService;
use App\Services\Dispatch\WhatsAppDispatchStatusMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookService
{
    public function __construct(
        private readonly WhatsAppDispatchStatusMapper $statusMapper,
        private readonly DocumentDispatchService $documentDispatchService,
    ) {}

    public function handle(?string $event, Request $request): void
    {
        $this->authorize($request);

        $payload = $request->all();
        $event = filled($event) ? $event : $this->resolveEvent($payload);

        $instanceKey = $this->resolveInstanceKey($payload);

        Log::info('Webhook WhatsApp recebido.', [
            'event' => $event,
            'instance_key' => $instanceKey,
            'payload' => $payload,
        ]);

        if (! filled($instanceKey)) {
            Log::warning('Webhook WhatsApp recebido sem instanceKey.', ['event' => $event]);
            return;
        }

        $instance = WhatsAppInstance::query()->where('instance_key', $instanceKey)->first();

        if (! $instance) {
            Log::warning('Webhook WhatsApp recebido para instancia desconhecida.', [
                'event' => $event,
                'instance_key' => $instanceKey,
            ]);
            return;
        }

        $instance->last_webhook_at = now();

        $eventNormalized = strtolower(trim((string) $event));

        if (in_array($eventNormalized, ['qrcode-updated', 'qrcode_updated', 'qrcodeupdated'], true)) {
            $qr = $this->resolveQrCodeBase64($payload);

            if (filled($qr)) {
                $instance->last_qr_code_base64 = $qr;
                $instance->status = OwnerWhatsAppInstanceService::STATUS_CONNECTING;
            }

            Log::info('Webhook WhatsApp QR code recebido.', [
                'event' => $eventNormalized,
                'instance_key' => $instance->instance_key,
                'has_qr' => filled($qr),
            ]);
        }

        if (in_array($eventNormalized, ['connection-update', 'connection_update', 'connectionupdate'], true)) {
            $state = $this->resolveConnectionState($payload);

            if (filled($state)) {
                $instance->last_connection_state = $state;
                $instance->status = $state;

                if ($state === OwnerWhatsAppInstanceService::STATUS_CONNECTED) {
                    $instance->connected_at = $instance->connected_at ?: now();
                }
            }

            Log::info('Webhook WhatsApp connection-update recebido.', [
                'event' => $eventNormalized,
                'instance_key' => $instance->instance_key,
                'state' => $state,
            ]);
        }

        $instance->save();

        $this->handleMessageEvent($event, $payload, $instance);
    }

    private function handleMessageEvent(?string $event, array $payload, WhatsAppInstance $instance): void
    {
        $eventNameRaw = $event ? strtolower(trim($event)) : 'unknown';
        $eventNameNormalized = str_replace(['.', '-'], '_', $eventNameRaw);

        if (! in_array($eventNameNormalized, ['messages_update'], true)) {
            return;
        }

        $messageId = $this->resolveMessageId($payload);
        $messageStatus = $this->resolveMessageStatus($payload);

        if (! $messageId) {
            Log::warning('Webhook WhatsApp recebido sem messageId.', [
                'event' => $eventNameRaw,
                'instance_key' => $instance->instance_key,
            ]);
            return;
        }

        $dispatchMessage = DocumentDispatchMessage::query()
            ->where('provider', 'whatsapp_gateway')
            ->where('provider_message_id', $messageId)
            ->first();

        if (! $dispatchMessage) {
            $outbox = WhatsAppOutboxMessage::query()
                ->where('provider_message_id', $messageId)
                ->first();

            if ($outbox) {
                $dispatchMessageId = $outbox->payload['dispatch_message_id'] ?? null;

                if (is_numeric($dispatchMessageId)) {
                    $dispatchMessage = DocumentDispatchMessage::query()->find((int) $dispatchMessageId);
                }
            }

            if (! $dispatchMessage) {
                Log::info('Webhook WhatsApp sem dispatch message correspondente.', [
                    'event' => $eventNameRaw,
                    'message_id' => $messageId,
                ]);
                return;
            }
        }

        $dispatch = $dispatchMessage->dispatch;

        if (! $dispatch) {
            Log::warning('Webhook WhatsApp sem dispatch associado.', [
                'event' => $eventNameRaw,
                'message_id' => $messageId,
            ]);
            return;
        }

        $normalizedStatus = $this->statusMapper->mapEvent($eventNameNormalized, $messageStatus);

        $eventKey = $this->resolveEventKey($payload, $eventNameNormalized, $messageId, $messageStatus);

        $eventModel = DocumentDispatchEvent::query()->firstOrCreate(
            [
                'provider' => 'whatsapp_gateway',
                'event_key' => $eventKey,
            ],
            [
                'owner_id' => $dispatch->owner_id,
                'document_dispatch_id' => $dispatch->getKey(),
                'document_dispatch_message_id' => $dispatchMessage->getKey(),
                'event_name' => $eventNameNormalized,
                'provider_message_id' => $messageId,
                'normalized_status' => $normalizedStatus,
                'occurred_at' => $this->resolveOccurredAt($payload),
                'received_at' => now(),
                'payload' => $payload,
            ]
        );

        if (! $eventModel->wasRecentlyCreated) {
            return;
        }

        if ($normalizedStatus) {
            $this->documentDispatchService->updateMessageStatus(
                message: $dispatchMessage,
                status: $normalizedStatus,
                providerStatusRaw: $messageStatus ?: $eventName,
                metadata: [
                    'webhook_event' => $eventName,
                ]
            );
        }
    }

    private function resolveEvent(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'event'),
            Arr::get($payload, 'type'),
            Arr::get($payload, 'data.event'),
            Arr::get($payload, 'data.type'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && filled($value)) {
                return $value;
            }
        }

        return null;
    }

    private function resolveMessageId(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'data.keyId'),
            Arr::get($payload, 'key.id'),
            Arr::get($payload, 'data.key.id'),
            Arr::get($payload, 'message.key.id'),
            Arr::get($payload, 'data.message.key.id'),
            Arr::get($payload, 'messageId'),
            Arr::get($payload, 'data.messageId'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveMessageStatus(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'status'),
            Arr::get($payload, 'data.status'),
            Arr::get($payload, 'update.status'),
            Arr::get($payload, 'data.update.status'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveEventKey(
        array $payload,
        string $eventName,
        string $messageId,
        ?string $messageStatus = null
    ): string
    {
        $eventId = Arr::get($payload, 'eventId') ?? Arr::get($payload, 'event_id');

        if (is_string($eventId) && $eventId !== '') {
            return $eventId;
        }

        $timestamp = Arr::get($payload, 'timestamp')
            ?? Arr::get($payload, 'ts')
            ?? Arr::get($payload, 'date_time')
            ?? Arr::get($payload, 'data.date_time');

        return sha1(implode('|', [
            'whatsapp_gateway',
            $eventName,
            $messageId,
            (string) ($messageStatus ?? ''),
            (string) $timestamp,
        ]));
    }

    private function resolveOccurredAt(array $payload): ?Carbon
    {
        $timestamp = Arr::get($payload, 'timestamp')
            ?? Arr::get($payload, 'ts')
            ?? Arr::get($payload, 'date_time')
            ?? Arr::get($payload, 'data.date_time');

        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestamp((int) $timestamp);
        }

        if (is_string($timestamp) && $timestamp !== '') {
            return Carbon::parse($timestamp);
        }

        return null;
    }

    private function authorize(Request $request): void
    {
        $secret = (string) (config('services.whatsapp_gateway.webhook_secret') ?? '');

        if (! filled($secret)) {
            return;
        }

        $authHeader = (string) ($request->header('authorization') ?? '');

        if ($authHeader === "Bearer {$secret}") {
            return;
        }

        abort(401);
    }

    private function resolveInstanceKey(array $payload): ?string
    {
        $candidate = Arr::get($payload, 'instance');

        if (is_string($candidate) && filled($candidate)) {
            return $candidate;
        }

        $candidates = [
            Arr::get($payload, 'instance.instanceName'),
            Arr::get($payload, 'instanceName'),
            Arr::get($payload, 'data.instanceName'),
            Arr::get($payload, 'data.instance.instanceName'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && filled($value)) {
                return $value;
            }
        }

        return null;
    }

    private function resolveQrCodeBase64(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'qrcode'),
            Arr::get($payload, 'qr'),
            Arr::get($payload, 'data.qrcode'),
            Arr::get($payload, 'data.qr'),
            Arr::get($payload, 'data.base64'),
            Arr::get($payload, 'qrcode.base64'),
            Arr::get($payload, 'data.qrcode.base64'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && filled($value)) {
                return $this->stripDataUrlPrefix($value);
            }
        }

        return null;
    }

    private function resolveConnectionState(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'state'),
            Arr::get($payload, 'instance.state'),
            Arr::get($payload, 'data.state'),
            Arr::get($payload, 'data.instance.state'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && filled($value)) {
                return strtolower($value);
            }
        }

        return null;
    }

    private function stripDataUrlPrefix(string $base64): string
    {
        if (str_starts_with($base64, 'data:image')) {
            $parts = explode(',', $base64, 2);
            return $parts[1] ?? $base64;
        }

        return $base64;
    }
}
