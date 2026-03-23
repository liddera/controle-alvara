<?php

namespace App\Services;

use App\Models\Alvara;
use App\DTOs\AlvaraDTO;

class AlvaraService
{
    public function criar(AlvaraDTO $dto): Alvara
    {
        $status = $this->calcularStatus($dto->data_vencimento);

        $alvara = Alvara::create([
            ...$dto->toArray(),
            'status' => $status,
        ]);

        // Aqui poderíamos disparar: event(new AlvaraCriado($alvara));
        
        return $alvara;
    }

    public function atualizar(Alvara $alvara, AlvaraDTO $dto): Alvara
    {
        $status = $this->calcularStatus($dto->data_vencimento);

        $alvara->update([
            ...$dto->toArray(),
            'status' => $status,
        ]);

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
