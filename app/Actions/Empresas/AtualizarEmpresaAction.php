<?php

namespace App\Actions\Empresas;

use App\Services\EmpresaService;
use App\DTOs\EmpresaDTO;
use Illuminate\Http\Request;
use App\Models\Empresa;

class AtualizarEmpresaAction
{
    public function __construct(private EmpresaService $service) {}

    public function execute(Empresa $empresa, Request $request): Empresa
    {
        $dto = EmpresaDTO::fromRequest($request);
        return $this->service->atualizar($empresa, $dto);
    }
}
