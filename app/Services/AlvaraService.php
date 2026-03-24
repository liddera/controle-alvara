<?php

namespace App\Services;

use App\Models\Alvara;
use App\DTOs\AlvaraDTO;

class AlvaraService
{
    public function criar(AlvaraDTO $dto): Alvara
    {
        $data = $dto->toArray();
        
        // Se tipo estiver vazio, busca do relacionamento para manter consistência legacy
        if (empty($data['tipo']) && !empty($data['tipo_alvara_id'])) {
            $data['tipo'] = \App\Models\TipoAlvara::find($data['tipo_alvara_id'])?->nome;
        }

        return Alvara::create($data);
    }

    public function atualizar(Alvara $alvara, AlvaraDTO $dto): Alvara
    {
        $data = $dto->toArray();

        // Se tipo estiver vazio, busca do relacionamento
        if (empty($data['tipo']) && !empty($data['tipo_alvara_id'])) {
            $data['tipo'] = \App\Models\TipoAlvara::find($data['tipo_alvara_id'])?->nome;
        }

        $alvara->update($data);

        return $alvara;
    }

    public function excluir(Alvara $alvara): void
    {
        $alvara->delete();
    }

    private function calcularStatus(string $vencimento): string
    {
        $data = \Carbon\Carbon::parse($vencimento);
        
        if ($data < now()) {
            return 'vencido';
        }

        if ($data <= now()->addDays(30)) {
            return 'proximo';
        }

        return 'vigente';
    }
}
