<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cnpj' => $this->cnpj,
            'responsavel' => $this->responsavel,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'total_alvaras' => $this->alvaras_count ?? $this->alvaras()->count(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
