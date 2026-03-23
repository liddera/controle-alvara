<?php

namespace App\Services;

use App\Models\Empresa;
use App\DTOs\EmpresaDTO;

class EmpresaService
{
    public function criar(EmpresaDTO $dto): Empresa
    {
        return Empresa::create($dto->toArray());
    }

    public function atualizar(Empresa $empresa, EmpresaDTO $dto): Empresa
    {
        $empresa->update($dto->toArray());
        return $empresa;
    }

    public function excluir(Empresa $empresa): void
    {
        $empresa->delete();
    }
}
