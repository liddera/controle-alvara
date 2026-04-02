<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class EmpresaDTO
{
    public function __construct(
        public int $user_id,
        public string $nome,
        public string $cnpj,
        public string $responsavel,
        public string $telefone,
        public string $email,
        public array $tipos_alvara = [],
        public array $datas_vencimento = [],
        public array $anexos = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user_id: auth()->id(),
            nome: $request->validated('nome'),
            cnpj: $request->validated('cnpj'),
            responsavel: $request->validated('responsavel'),
            telefone: $request->validated('telefone'),
            email: $request->validated('email'),
            tipos_alvara: $request->validated('tipos_alvara', []),
            datas_vencimento: $request->validated('datas_vencimento', []),
            anexos: $request->file('anexos', []),
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'nome' => $this->nome,
            'cnpj' => $this->cnpj,
            'responsavel' => $this->responsavel,
            'telefone' => $this->telefone,
            'email' => $this->email,
        ];
    }
}
