<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Actions\ExportAlvarasAction;
use App\DTOs\AlvaraFilterDTO;
use App\Http\Requests\FilterAlvaraRequest;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected ExportAlvarasAction $exportAction
    ) {}

    public function index(FilterAlvaraRequest $request)
    {
        if ($request->user()->hasRole('super-admin')) {
            return redirect()->to('/admin');
        }

        $dto = AlvaraFilterDTO::fromRequest($request);
        
        $empresas = $this->dashboardService->getEmpresasWithCount();
        
        // Se o usuário selecionou uma empresa específica ou é nulo (Todas as Empresas)
        $empresaSelecionada = $dto->empresa_id 
            ? $empresas->where('id', $dto->empresa_id)->first() 
            : null;

        $alvaras = $this->dashboardService->getFilteredAlvaras($dto);
        $stats = $this->dashboardService->getStats($dto);
        $tiposAlvara = \App\Models\TipoAlvara::all();
        
        return view('dashboard', compact('empresas', 'empresaSelecionada', 'alvaras', 'stats', 'tiposAlvara'));
    }

    public function export(FilterAlvaraRequest $request)
    {
        $dto = AlvaraFilterDTO::fromRequest($request);
        return $this->exportAction->execute($dto);
    }
}
