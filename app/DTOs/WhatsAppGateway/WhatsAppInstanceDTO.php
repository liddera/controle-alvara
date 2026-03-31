<?php

namespace App\DTOs\WhatsAppGateway;

readonly class WhatsAppInstanceDTO
{
    public function __construct(
        public string $instanceKey,
        public ?string $instanceId,
        public ?string $apiKey,
        public array $raw = [],
    ) {}
}

