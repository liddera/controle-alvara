<?php

namespace App\Services;

use App\Models\Empresa;
use App\DTOs\EmpresaDTO;

class EmpresaService
{
    public function criar(EmpresaDTO $dto): Empresa
    {
        $empresa = Empresa::create($dto->toArray());
        $this->syncAlvaras($empresa, $dto);
        return $empresa;
    }

    public function atualizar(Empresa $empresa, EmpresaDTO $dto): Empresa
    {
        $empresa->update($dto->toArray());
        $this->syncAlvaras($empresa, $dto);
        return $empresa;
    }

    public function excluir(Empresa $empresa): void
    {
        $empresa->delete();
    }

    private function syncAlvaras(Empresa $empresa, EmpresaDTO $dto): void
    {
        if (empty($dto->tipos_alvara)) {
            $empresa->tiposAlvara()->sync([]);
            return;
        }

        $empresa->tiposAlvara()->sync($dto->tipos_alvara);

        foreach ($dto->tipos_alvara as $tipoId) {
            if (!empty($dto->datas_vencimento[$tipoId])) {
                $tipoNome = \App\Models\TipoAlvara::find($tipoId)?->nome ?? 'Outro';
                
                // Calcular o status com base na data de vencimento
                $hoje = now()->startOfDay();
                $vencimento = \Carbon\Carbon::parse($dto->datas_vencimento[$tipoId])->startOfDay();
                $diasParaVencer = $hoje->diffInDays($vencimento, false); // false para retornar negativo se já passou

                if ($diasParaVencer < 0) {
                    $status = 'vencido';
                } elseif ($diasParaVencer <= 30) {
                    $status = 'proximo';
                } else {
                    $status = 'vigente';
                }
                
                \App\Models\Alvara::updateOrCreate([
                    'empresa_id' => $empresa->id,
                    'tipo_alvara_id' => $tipoId,
                ], [
                    'user_id' => $dto->user_id,
                    'owner_id' => auth()->user()->owner_id ?? $dto->user_id,
                    'tipo' => $tipoNome,
                    'data_vencimento' => $dto->datas_vencimento[$tipoId],
                    'status' => $status
                ]);
            }
        }
    }
}
