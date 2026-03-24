<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class AlvaraFilterDTO
{
    public function __construct(
        public ?int $empresa_id,
        public ?string $search,
        public ?string $status
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            empresa_id: $request->integer('empresa_id') ?: null,
            search: $request->string('search')->trim() ?: null,
            status: $request->string('status')->trim() ?: 'todos'
        );
    }
}
