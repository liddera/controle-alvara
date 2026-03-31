<?php

namespace App\DTOs;

class TransactionalEmailResultDTO
{
    public function __construct(
        public readonly ?string $messageId,
        public readonly array $raw = [],
    ) {}
}
