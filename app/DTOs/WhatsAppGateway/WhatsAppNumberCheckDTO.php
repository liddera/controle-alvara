<?php

namespace App\DTOs\WhatsAppGateway;

readonly class WhatsAppNumberCheckDTO
{
    /**
     * @param array<int, array{number: string, exists: bool, jid?: string|null}> $results
     */
    public function __construct(
        public array $results,
        public array $raw = [],
    ) {}

    public function allExist(): bool
    {
        foreach ($this->results as $result) {
            if (! ($result['exists'] ?? false)) {
                return false;
            }
        }

        return true;
    }
}

