<?php

namespace App\Integrations\WhatsAppGateway;

use App\Contracts\WhatsApp\WhatsAppGateway;
use App\DTOs\WhatsAppGateway\WhatsAppConnectionStateDTO;
use App\DTOs\WhatsAppGateway\WhatsAppConnectDTO;
use App\DTOs\WhatsAppGateway\WhatsAppInstanceDTO;
use App\DTOs\WhatsAppGateway\WhatsAppNumberCheckDTO;
use App\DTOs\WhatsAppGateway\WhatsAppSendResultDTO;

class NullWhatsAppGateway implements WhatsAppGateway
{
    public function createInstance(string $instanceKey, array $webhookConfig = []): WhatsAppInstanceDTO
    {
        $this->fail();
    }

    public function connect(string $instanceKey, ?string $apiKey = null): WhatsAppConnectDTO
    {
        $this->fail();
    }

    public function getConnectionState(string $instanceKey, ?string $apiKey = null): WhatsAppConnectionStateDTO
    {
        $this->fail();
    }

    public function logout(string $instanceKey, ?string $apiKey = null): void
    {
        $this->fail();
    }

    public function sendText(string $instanceKey, string $toNumber, string $text, ?string $apiKey = null): WhatsAppSendResultDTO
    {
        $this->fail();
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
        $this->fail();
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
        $this->fail();
    }

    public function checkNumbers(string $instanceKey, array $numbers, ?string $apiKey = null): WhatsAppNumberCheckDTO
    {
        $this->fail();
    }

    private function fail(): never
    {
        throw new \RuntimeException('WhatsApp Gateway nao configurado.');
    }
}
