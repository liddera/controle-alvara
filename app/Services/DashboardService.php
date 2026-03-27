<?php

namespace App\Services;

use App\DTOs\AlvaraFilterDTO;
use App\Models\Alvara;
use App\Models\Empresa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
        // Os cards do topo devem respeitar o contexto atual (empresa, tipo, período etc.),
        // mas não podem encolher quando a listagem é filtrada por status.
        $query = Alvara::filterByDto($dto->withoutStatus());

        return [
            'total' => (clone $query)->count(),
            'ativos' => (clone $query)->vigente()->count(),
            'em_renovacao' => (clone $query)->emRenovacao()->count(),
            'vencidos' => (clone $query)->vencido()->count(),
        ];
    }
}
