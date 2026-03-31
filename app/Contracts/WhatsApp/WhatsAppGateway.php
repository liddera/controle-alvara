<?php

namespace App\Contracts\WhatsApp;

use App\DTOs\WhatsAppGateway\WhatsAppConnectionStateDTO;
use App\DTOs\WhatsAppGateway\WhatsAppConnectDTO;
use App\DTOs\WhatsAppGateway\WhatsAppInstanceDTO;
use App\DTOs\WhatsAppGateway\WhatsAppNumberCheckDTO;
use App\DTOs\WhatsAppGateway\WhatsAppSendResultDTO;

interface WhatsAppGateway
{
    public function createInstance(string $instanceKey, array $webhookConfig = []): WhatsAppInstanceDTO;

    public function connect(string $instanceKey, ?string $apiKey = null): WhatsAppConnectDTO;

    public function getConnectionState(string $instanceKey, ?string $apiKey = null): WhatsAppConnectionStateDTO;

    public function logout(string $instanceKey, ?string $apiKey = null): void;

    public function sendText(string $instanceKey, string $toNumber, string $text, ?string $apiKey = null): WhatsAppSendResultDTO;

    public function sendDocumentByUrl(
        string $instanceKey,
        string $toNumber,
        string $fileUrl,
        string $fileName,
        string $mimeType,
        ?string $caption = null,
        ?string $apiKey = null
    ): WhatsAppSendResultDTO;

    /**
     * Sends a document where the media payload is a base64-encoded string (no URL fetch required).
     */
    public function sendDocumentByBase64(
        string $instanceKey,
        string $toNumber,
        string $base64,
        string $fileName,
        string $mimeType,
        ?string $caption = null,
        ?string $apiKey = null
    ): WhatsAppSendResultDTO;

    public function checkNumbers(string $instanceKey, array $numbers, ?string $apiKey = null): WhatsAppNumberCheckDTO;
}
