<?php

namespace App\Services\Email;

use App\Models\DocumentDispatchEvent;
use App\Models\DocumentDispatchMessage;
use App\Services\Dispatch\DocumentDispatchService;
use App\Services\Dispatch\EmailDispatchStatusMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EmailWebhookService
{
    public function __construct(
        private readonly EmailDispatchStatusMapper $statusMapper,
        private readonly DocumentDispatchService $documentDispatchService,
    ) {}

    public function handle(Request $request): void
    {
        $this->authorize($request);

        $payload = $request->all();

        foreach ($this->normalizeEvents($payload) as $eventPayload) {
            $this->handleEvent($eventPayload);
        }
    }

    private function handleEvent(array $payload): void
    {
        $eventName = $this->resolveEventName($payload);
        $messageId = $this->resolveMessageId($payload);
        $custom = $this->parseCustomHeader($this->resolveCustomHeader($payload));

        $dispatchMessageId = isset($custom['dispatch_message_id']) ? (int) $custom['dispatch_message_id'] : null;

        $message = $this->resolveMessage($messageId, $dispatchMessageId);

        if (! $message) {
            Log::warning('Webhook email recebido sem dispatch message identificavel.', [
                'event' => $eventName,
                'message_id' => $messageId,
            ]);
            return;
        }

        $dispatch = $message->dispatch;

        if (! $dispatch) {
            Log::warning('Webhook email recebido sem dispatch.', [
                'event' => $eventName,
                'message_id' => $messageId,
            ]);
            return;
        }

        $eventKey = $this->resolveEventKey($payload, $eventName, $messageId);
        $normalizedStatus = $this->statusMapper->mapEvent($eventName);

        $event = DocumentDispatchEvent::query()->firstOrCreate(
            [
                'provider' => 'email_provider',
                'event_key' => $eventKey,
            ],
            [
                'owner_id' => $dispatch->owner_id,
                'document_dispatch_id' => $dispatch->getKey(),
                'document_dispatch_message_id' => $message->getKey(),
                'event_name' => $eventName,
                'provider_message_id' => $messageId,
                'normalized_status' => $normalizedStatus,
                'occurred_at' => $this->resolveOccurredAt($payload),
                'received_at' => now(),
                'payload' => $payload,
            ]
        );

        if (! $event->wasRecentlyCreated) {
            return;
        }

        if ($normalizedStatus) {
            $this->documentDispatchService->updateMessageStatus(
                message: $message,
                status: $normalizedStatus,
                providerStatusRaw: $eventName,
                metadata: [
                    'webhook_event' => $eventName,
                ]
            );
        }
    }

    private function normalizeEvents(array $payload): array
    {
        if (isset($payload[0]) && is_array($payload[0])) {
            return $payload;
        }

        return [$payload];
    }

    private function resolveEventName(array $payload): string
    {
        return (string) (Arr::get($payload, 'event')
            ?? Arr::get($payload, 'type')
            ?? Arr::get($payload, 'data.event')
            ?? 'unknown');
    }

    private function resolveMessageId(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'message-id'),
            Arr::get($payload, 'messageId'),
            Arr::get($payload, 'message_id'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveCustomHeader(array $payload): ?string
    {
        $candidates = [
            Arr::get($payload, 'X-Mailin-custom'),
            Arr::get($payload, 'x-mailin-custom'),
            Arr::get($payload, 'x_mailin_custom'),
            Arr::get($payload, 'custom'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function parseCustomHeader(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $parts = array_filter(array_map('trim', explode(';', $value)));
        $result = [];

        foreach ($parts as $part) {
            [$key, $raw] = array_pad(explode('=', $part, 2), 2, null);

            if (! $key || $raw === null) {
                continue;
            }

            $result[strtolower(trim($key))] = trim($raw);
        }

        return $result;
    }

    private function resolveEventKey(array $payload, string $eventName, ?string $messageId): string
    {
        $eventId = Arr::get($payload, 'eventId') ?? Arr::get($payload, 'event_id');

        if (is_string($eventId) && $eventId !== '') {
            return $eventId;
        }

        $timestamp = Arr::get($payload, 'date') ?? Arr::get($payload, 'timestamp') ?? Arr::get($payload, 'ts');
        $email = Arr::get($payload, 'email');

        return sha1(implode('|', [
            'email_provider',
            $eventName,
            $messageId ?? 'unknown',
            (string) $timestamp,
            (string) $email,
        ]));
    }

    private function resolveOccurredAt(array $payload): ?Carbon
    {
        $timestamp = Arr::get($payload, 'date') ?? Arr::get($payload, 'timestamp') ?? Arr::get($payload, 'ts');

        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestamp((int) $timestamp);
        }

        if (is_string($timestamp) && $timestamp !== '') {
            return Carbon::parse($timestamp);
        }

        return null;
    }

    private function resolveMessage(?string $messageId, ?int $dispatchMessageId): ?DocumentDispatchMessage
    {
        if ($dispatchMessageId) {
            return DocumentDispatchMessage::query()->find($dispatchMessageId);
        }

        if ($messageId) {
            return DocumentDispatchMessage::query()
                ->where('provider', 'email_provider')
                ->where('provider_message_id', $messageId)
                ->first();
        }

        return null;
    }

    private function authorize(Request $request): void
    {
        $secret = (string) (config('services.email_provider.webhook_secret') ?? '');

        if (! filled($secret)) {
            return;
        }

        $authHeader = (string) ($request->header('authorization') ?? '');

        if ($authHeader === "Bearer {$secret}") {
            return;
        }

        abort(401);
    }
}
