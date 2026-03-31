<?php

namespace App\DTOs\WhatsAppGateway;

readonly class WhatsAppSendResultDTO
{
    public function __construct(
        public ?string $messageId,
        public ?string $remoteJid,
        public ?string $status,
        public array $raw = [],
    ) {}
}

