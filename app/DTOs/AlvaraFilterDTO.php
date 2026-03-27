<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class AlvaraFilterDTO
{
    public function __construct(
        public ?int $empresa_id,
        public ?int $tipo_alvara_id,
        public ?string $tipo_slug,
        public ?string $search,
        public ?string $status,
        public ?string $vencimento_de,
        public ?string $vencimento_ate
    ) {}

    public static function fromRequest(Request $request): self
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $tipoSlug = trim((string) $request->input('tipo', ''));
        $vencimentoDe = trim((string) $request->input('vencimento_de', ''));
        $vencimentoAte = trim((string) $request->input('vencimento_ate', ''));

        return new self(
            empresa_id: $request->integer('empresa_id') ?: null,
            tipo_alvara_id: $request->integer('tipo_alvara_id') ?: null,
            tipo_slug: $tipoSlug !== '' ? $tipoSlug : null,
            search: $search !== '' ? $search : null,
            status: $status !== '' ? $status : 'todos',
            vencimento_de: $vencimentoDe !== '' ? $vencimentoDe : null,
            vencimento_ate: $vencimentoAte !== '' ? $vencimentoAte : null,
        );
    }

    public function withoutStatus(): self
    {
        return new self(
            empresa_id: $this->empresa_id,
            tipo_alvara_id: $this->tipo_alvara_id,
            tipo_slug: $this->tipo_slug,
            search: $this->search,
            status: 'todos',
            vencimento_de: $this->vencimento_de,
            vencimento_ate: $this->vencimento_ate,
        );
    }
}
