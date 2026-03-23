<?php

namespace App\Actions\Alvaras;

use App\Services\AlvaraService;
use App\Models\Alvara;

class ExcluirAlvaraAction
{
    public function __construct(private AlvaraService $service) {}

    public function execute(Alvara $alvara): void
    {
        $this->service->excluir($alvara);
    }
}
