<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlvaraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'tipo' => $this->tipo,
            'numero' => $this->numero,
            'data_emissao' => $this->data_emissao ? $this->data_emissao->format('Y-m-d') : null,
            'data_vencimento' => $this->data_vencimento->format('Y-m-d'),
            'status' => $this->status,
            'observacoes' => $this->observacoes,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
