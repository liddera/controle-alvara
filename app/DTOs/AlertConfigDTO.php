<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class AlertConfigDTO
{
    public function __construct(
        public ?int $tipo_alvara_id,
        public int $days_before,
        public bool $is_active = true,
        public array $recipient_emails = [],
        public array $recipient_phones = [],
    ) {}

    public static function fromRequest($request): self
    {
        // Se for um FormRequest, usamos validated(), senão usamos input()
        $data = method_exists($request, 'validated') ? $request->validated() : $request->all();

        return new self(
            tipo_alvara_id: $data['tipo_alvara_id'] ?? null,
            days_before: (int) ($data['days_before'] ?? 0),
            is_active: (bool) ($data['is_active'] ?? true),
            recipient_emails: $data['recipient_emails'] ?? [],
            recipient_phones: $data['recipient_phones'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'tipo_alvara_id' => $this->tipo_alvara_id,
            'days_before' => $this->days_before,
            'is_active' => $this->is_active,
            'recipient_emails' => $this->recipient_emails,
            'recipient_phones' => $this->recipient_phones,
        ];
    }
}
