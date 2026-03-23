<?php

namespace App\Actions\Empresas;

use App\Services\EmpresaService;
use App\DTOs\EmpresaDTO;
use Illuminate\Http\Request;
use App\Models\Empresa;

class CriarEmpresaAction
{
    public function __construct(private EmpresaService $service) {}

    public function execute(Request $request): Empresa
    {
        $dto = EmpresaDTO::fromRequest($request);
        return $this->service->criar($dto);
    }
}
