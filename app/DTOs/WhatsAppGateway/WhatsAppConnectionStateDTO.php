<?php

namespace App\DTOs\WhatsAppGateway;

readonly class WhatsAppConnectionStateDTO
{
    public function __construct(
        public string $state,
        public array $raw = [],
    ) {}
}

