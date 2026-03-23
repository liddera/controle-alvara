<?php

namespace App\Actions\Alvaras;

use App\Services\AlvaraService;
use App\DTOs\AlvaraDTO;
use Illuminate\Http\Request;
use App\Models\Alvara;

class AtualizarAlvaraAction
{
    public function __construct(private AlvaraService $service) {}

    public function execute(Alvara $alvara, Request $request): Alvara
    {
        $dto = AlvaraDTO::fromRequest($request);
        return $this->service->atualizar($alvara, $dto);
    }
}
