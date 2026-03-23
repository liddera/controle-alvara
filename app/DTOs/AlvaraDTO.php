<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class AlvaraDTO
{
    public function __construct(
        public int $empresa_id,
        public int $user_id,
        public string $tipo,
        public ?string $numero,
        public ?string $data_emissao,
        public string $data_vencimento,
        public ?string $observacoes,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            empresa_id: $request->validated('empresa_id'),
            user_id: auth()->id() ?? $request->validated('user_id'), // Fallback for testing/seeding without auth
            tipo: $request->validated('tipo'),
            numero: $request->validated('numero'),
            data_emissao: $request->validated('data_emissao'),
            data_vencimento: $request->validated('data_vencimento'),
            observacoes: $request->validated('observacoes'),
        );
    }

    public function toArray(): array
    {
        return [
            'empresa_id' => $this->empresa_id,
            'user_id' => $this->user_id,
            'tipo' => $this->tipo,
            'numero' => $this->numero,
            'data_emissao' => $this->data_emissao,
            'data_vencimento' => $this->data_vencimento,
            'observacoes' => $this->observacoes,
        ];
    }
}
