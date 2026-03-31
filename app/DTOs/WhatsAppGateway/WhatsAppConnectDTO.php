<?php

namespace App\DTOs\WhatsAppGateway;

readonly class WhatsAppConnectDTO
{
    public function __construct(
        public ?string $pairingCode,
        public ?string $qrCodeBase64,
        public ?string $code,
        public ?int $count,
        public array $raw = [],
    ) {}
}
