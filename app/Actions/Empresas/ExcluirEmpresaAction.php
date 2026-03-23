<?php

namespace App\Actions\Empresas;

use App\Services\EmpresaService;
use App\Models\Empresa;

class ExcluirEmpresaAction
{
    public function __construct(private EmpresaService $service) {}

    public function execute(Empresa $empresa): void
    {
        $this->service->excluir($empresa);
    }
}
