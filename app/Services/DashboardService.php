<?php

namespace App\Services;

use App\Models\Alvara;
use App\Models\Empresa;
use App\DTOs\AlvaraFilterDTO;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DashboardService
{
    public function getEmpresasWithCount(): Collection
    {
        return Empresa::withCount('alvaras')->latest()->get();
    }

    public function getFilteredAlvaras(AlvaraFilterDTO $dto): LengthAwarePaginator
    {
        return Alvara::with(['empresa', 'tipoAlvara'])->filterByDto($dto)->latest()->paginate(10);
    }

    public function getStats(AlvaraFilterDTO $dto): array
    {
        // Garantimos que os contadores respeitem a empresa e busca atual
        // Note: O Total geralmente ignora o filtro de status mas respeita o de empresa/busca,
        // mas para ser 100% coerente com o que o usuário vê, vamos filtrar tudo.
        $query = Alvara::filterByDto($dto);

        return [
            'total' => (clone $query)->count(),
            'ativos' => (clone $query)->vigente()->count(),
            'em_renovacao' => (clone $query)->emRenovacao()->count(),
            'vencidos' => (clone $query)->vencido()->count(),
        ];
    }
}
