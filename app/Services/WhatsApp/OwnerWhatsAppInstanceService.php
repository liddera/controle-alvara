<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsApp\WhatsAppGateway;
use App\Models\User;
use App\Models\WhatsAppInstance;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OwnerWhatsAppInstanceService
{
    public const STATUS_MISCONFIGURED = 'misconfigured';
    public const STATUS_CONNECTED = 'open';
    public const STATUS_DISCONNECTED = 'close';
    public const STATUS_CONNECTING = 'connecting';
    public const STATUS_CREATED = 'created';
    public const STATUS_UNKNOWN = 'unknown';

    public function __construct(private WhatsAppGateway $gateway) {}

    public function getConnectionStatus(int $ownerId): string
    {
        try {
            $instance = $this->findForOwner($ownerId);

            if (! $instance) {
                return self::STATUS_DISCONNECTED;
            }

            if (! filled(config('services.whatsapp_gateway.base_url')) || ! filled(config('services.whatsapp_gateway.api_key'))) {
                return self::STATUS_MISCONFIGURED;
            }

            return $instance->last_connection_state ?: ($instance->status ?: self::STATUS_UNKNOWN);
        } catch (\Throwable) {
            return self::STATUS_UNKNOWN;
        }
    }

    public function findForOwner(int $ownerId): ?WhatsAppInstance
    {
        return WhatsAppInstance::query()
            ->where('owner_id', $ownerId)
            ->first();
    }

    public function ensureInstanceForOwner(int $ownerId): WhatsAppInstance
    {
        $instanceKey = $this->instanceKeyForOwner($ownerId);
        $provider = (string) (config('services.whatsapp_gateway.provider') ?: 'http-v2');

        $instance = WhatsAppInstance::query()->firstOrCreate(
            ['owner_id' => $ownerId],
            [
                'provider' => $provider,
                'instance_key' => $instanceKey,
                'status' => self::STATUS_CREATED,
            ]
        );

        if (! filled($instance->instance_key)) {
            $instance->instance_key = $instanceKey;
        }

        if (! filled($instance->provider)) {
            $instance->provider = $provider;
        }

        if (! filled($instance->instance_id) || ! filled($instance->instance_api_key)) {
            $webhookConfig = $this->buildWebhookConfig();

            try {
                $created = $this->gateway->createInstance($instance->instance_key, $webhookConfig);

                $instance->instance_id = $created->instanceId ?: $instance->instance_id;
                $instance->instance_api_key = $created->apiKey ?: $instance->instance_api_key;
            } catch (\Throwable $exception) {
                if ($this->isInstanceNameAlreadyInUse($instance->instance_key, $exception)) {
                    Log::info('Instancia WhatsApp ja existe no gateway. Reutilizando instance_key.', [
                        'owner_id' => $ownerId,
                        'instance_key' => $instance->instance_key,
                    ]);
                } else {
                    throw $exception;
                }
            }

            $instance->status = self::STATUS_CREATED;
            $instance->save();
        }

        return $instance;
    }

    public function requestConnectionForOwner(int $ownerId): WhatsAppInstance
    {
        $instance = $this->ensureInstanceForOwner($ownerId);

        $connect = $this->gateway->connect($instance->instance_key, $instance->instance_api_key);

        $instance->last_pairing_code = $connect->pairingCode;
        $instance->last_qr_code_payload = $connect->code ?: $instance->last_qr_code_payload;
        $instance->last_qr_code_base64 = $connect->qrCodeBase64 ?: $instance->last_qr_code_base64;
        $instance->status = self::STATUS_CONNECTING;
        $instance->save();

        return $instance;
    }

    public function refreshConnectionStateForOwner(int $ownerId): WhatsAppInstance
    {
        $instance = $this->ensureInstanceForOwner($ownerId);

        try {
            $state = $this->gateway->getConnectionState($instance->instance_key, $instance->instance_api_key);
            $instance->last_connection_state = $state->state;
            $instance->status = $state->state;

            if ($state->state === self::STATUS_CONNECTED) {
                $instance->connected_at = $instance->connected_at ?: now();
            }

            $instance->save();
        } catch (\Throwable $exception) {
            Log::warning('Falha ao consultar estado do WhatsApp para owner.', [
                'owner_id' => $ownerId,
                'error' => $exception->getMessage(),
            ]);
        }

        return $instance;
    }

    public function disconnectOwner(int $ownerId): void
    {
        $instance = $this->findForOwner($ownerId);

        if (! $instance) {
            return;
        }

        $this->gateway->logout($instance->instance_key, $instance->instance_api_key);

        $instance->status = self::STATUS_DISCONNECTED;
        $instance->last_connection_state = self::STATUS_DISCONNECTED;
        $instance->last_qr_code_base64 = null;
        $instance->last_pairing_code = null;
        $instance->save();
    }

    public function instanceKeyForOwner(int $ownerId): string
    {
        $name = User::query()->whereKey($ownerId)->value('name');
        $firstName = is_string($name) ? trim(strtok($name, ' ')) : '';
        $slug = Str::slug($firstName ?: 'owner');

        return "{$slug}-{$ownerId}";
    }

    private function buildWebhookConfig(): array
    {
        $webhookUrl = (string) (config('services.whatsapp_gateway.webhook_url') ?? '');

        if (! filled($webhookUrl)) {
            return [];
        }

        $host = parse_url($webhookUrl, PHP_URL_HOST);

        if (in_array($host, ['localhost', '127.0.0.1'], true)) {
            return [];
        }

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $secret = (string) (config('services.whatsapp_gateway.webhook_secret') ?? '');

        if (filled($secret)) {
            $headers['authorization'] = "Bearer {$secret}";
        }

        return [
            'url' => $webhookUrl,
            'byEvents' => true,
            'base64' => true,
            'events' => ['QRCODE_UPDATED', 'CONNECTION_UPDATE', 'SEND_MESSAGE', 'MESSAGES_UPDATE'],
            'headers' => $headers,
        ];
    }

    private function isInstanceNameAlreadyInUse(string $instanceKey, \Throwable $exception): bool
    {
        if (! $exception instanceof RequestException) {
            return false;
        }

        if ($exception->response?->status() !== 403) {
            return false;
        }

        $data = $exception->response->json();

        $messages = [];

        if (is_array($data)) {
            $messages = array_merge(
                Arr::wrap(data_get($data, 'response.message')),
                Arr::wrap(data_get($data, 'message')),
                Arr::wrap(data_get($data, 'error')),
            );
        }

        foreach ($messages as $message) {
            if (! is_string($message)) {
                continue;
            }

            $normalized = strtolower($message);

            if (str_contains($normalized, 'already in use') && str_contains($normalized, strtolower($instanceKey))) {
                return true;
            }
        }

        return false;
    }
}
