<?php

namespace App\Integrations\WhatsAppGateway\HttpV2;

use App\Contracts\WhatsApp\WhatsAppGateway;
use App\DTOs\WhatsAppGateway\WhatsAppConnectionStateDTO;
use App\DTOs\WhatsAppGateway\WhatsAppConnectDTO;
use App\DTOs\WhatsAppGateway\WhatsAppInstanceDTO;
use App\DTOs\WhatsAppGateway\WhatsAppNumberCheckDTO;
use App\DTOs\WhatsAppGateway\WhatsAppSendResultDTO;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class WhatsAppGatewayHttpV2Client implements WhatsAppGateway
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly int $timeoutSeconds = 20,
    ) {}

    public function createInstance(string $instanceKey, array $webhookConfig = []): WhatsAppInstanceDTO
    {
        $payload = [
            'instanceName' => $instanceKey,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
            'groupsIgnore' => true,
        ];

        if (filled($webhookConfig['url'] ?? null)) {
            $payload['webhook'] = [
                'url' => (string) $webhookConfig['url'],
                'byEvents' => (bool) ($webhookConfig['byEvents'] ?? true),
                'base64' => (bool) ($webhookConfig['base64'] ?? true),
                'events' => $webhookConfig['events'] ?? ['QRCODE_UPDATED', 'CONNECTION_UPDATE'],
                'headers' => $webhookConfig['headers'] ?? [],
            ];
        }

        $response = $this->request($this->apiKey)
            ->post('/instance/create', $payload)
            ->throw()
            ->json();

        return new WhatsAppInstanceDTO(
            instanceKey: $instanceKey,
            instanceId: Arr::get($response, 'instance.instanceId'),
            apiKey: Arr::get($response, 'hash.apikey'),
            raw: is_array($response) ? $response : [],
        );
    }

    public function connect(string $instanceKey, ?string $apiKey = null): WhatsAppConnectDTO
    {
        $response = $this->request($apiKey ?: $this->apiKey)
            ->get("/instance/connect/{$instanceKey}")
            ->throw()
            ->json();

        $qrCodeBase64 = null;

        if (is_array($response)) {
            $candidates = [
                Arr::get($response, 'base64'),
                Arr::get($response, 'qrcode'),
                Arr::get($response, 'qrcode.base64'),
                Arr::get($response, 'data.base64'),
                Arr::get($response, 'data.qrcode'),
                Arr::get($response, 'data.qrcode.base64'),
            ];

            foreach ($candidates as $value) {
                if (is_string($value) && filled($value)) {
                    $qrCodeBase64 = $this->stripDataUrlPrefix($value);
                    break;
                }
            }
        }

        return new WhatsAppConnectDTO(
            pairingCode: Arr::get($response, 'pairingCode'),
            qrCodeBase64: $qrCodeBase64,
            code: Arr::get($response, 'code'),
            count: Arr::get($response, 'count'),
            raw: is_array($response) ? $response : [],
        );
    }

    public function getConnectionState(string $instanceKey, ?string $apiKey = null): WhatsAppConnectionStateDTO
    {
        $response = $this->request($apiKey ?: $this->apiKey)
            ->get("/instance/connectionState/{$instanceKey}")
            ->throw()
            ->json();

        $state = (string) (Arr::get($response, 'instance.state') ?? Arr::get($response, 'state') ?? 'unknown');
        $state = strtolower($state);

        return new WhatsAppConnectionStateDTO(
            state: $state,
            raw: is_array($response) ? $response : [],
        );
    }

    public function logout(string $instanceKey, ?string $apiKey = null): void
    {
        $this->request($apiKey ?: $this->apiKey)
            ->delete("/instance/logout/{$instanceKey}")
            ->throw();
    }

    public function sendText(string $instanceKey, string $toNumber, string $text, ?string $apiKey = null): WhatsAppSendResultDTO
    {
        $payload = [
            'number' => $toNumber,
            'text' => $text,
        ];

        $response = $this->request($apiKey ?: $this->apiKey)
            ->post("/message/sendText/{$instanceKey}", $payload)
            ->throw()
            ->json();

        return new WhatsAppSendResultDTO(
            messageId: Arr::get($response, 'key.id'),
            remoteJid: Arr::get($response, 'key.remoteJid'),
            status: Arr::get($response, 'status'),
            raw: is_array($response) ? $response : [],
        );
    }

    public function sendDocumentByUrl(
        string $instanceKey,
        string $toNumber,
        string $fileUrl,
        string $fileName,
        string $mimeType,
        ?string $caption = null,
        ?string $apiKey = null
    ): WhatsAppSendResultDTO {
        $payload = [
            'number' => $toNumber,
            'mediatype' => 'document',
            'mimetype' => $mimeType,
            'caption' => $caption,
            'media' => $fileUrl,
            'fileName' => $fileName,
        ];

        $response = $this->request($apiKey ?: $this->apiKey)
            ->post("/message/sendMedia/{$instanceKey}", $payload)
            ->throw()
            ->json();

        return new WhatsAppSendResultDTO(
            messageId: Arr::get($response, 'key.id'),
            remoteJid: Arr::get($response, 'key.remoteJid'),
            status: Arr::get($response, 'status'),
            raw: is_array($response) ? $response : [],
        );
    }

    public function sendDocumentByBase64(
        string $instanceKey,
        string $toNumber,
        string $base64,
        string $fileName,
        string $mimeType,
        ?string $caption = null,
        ?string $apiKey = null
    ): WhatsAppSendResultDTO {
        $payload = [
            'number' => $toNumber,
            'mediatype' => 'document',
            'mimetype' => $mimeType,
            'caption' => $caption,
            'media' => $base64,
            'fileName' => $fileName,
        ];

        $response = $this->request($apiKey ?: $this->apiKey)
            ->post("/message/sendMedia/{$instanceKey}", $payload)
            ->throw()
            ->json();

        return new WhatsAppSendResultDTO(
            messageId: Arr::get($response, 'key.id'),
            remoteJid: Arr::get($response, 'key.remoteJid'),
            status: Arr::get($response, 'status'),
            raw: is_array($response) ? $response : [],
        );
    }

    public function checkNumbers(string $instanceKey, array $numbers, ?string $apiKey = null): WhatsAppNumberCheckDTO
    {
        $payload = [
            'numbers' => array_values($numbers),
        ];

        $response = $this->request($apiKey ?: $this->apiKey)
            ->post("/chat/whatsappNumbers/{$instanceKey}", $payload)
            ->throw()
            ->json();

        $results = [];

        if (is_array($response)) {
            foreach ($response as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $results[] = [
                    'number' => (string) ($item['number'] ?? ''),
                    'exists' => (bool) ($item['exists'] ?? false),
                    'jid' => $item['jid'] ?? null,
                ];
            }
        }

        return new WhatsAppNumberCheckDTO(
            results: $results,
            raw: is_array($response) ? $response : [],
        );
    }

    private function request(string $apiKey): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->timeout($this->timeoutSeconds)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'apikey' => $apiKey,
            ]);
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
