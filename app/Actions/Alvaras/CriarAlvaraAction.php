<?php

namespace App\Actions\Alvaras;

use App\Services\AlvaraService;
use App\DTOs\AlvaraDTO;
use Illuminate\Http\Request;
use App\Models\Alvara;

class CriarAlvaraAction
{
    public function __construct(private AlvaraService $service) {}

    public function execute(Request $request): Alvara
    {
        $dto = AlvaraDTO::fromRequest($request);
        return $this->service->criar($dto);
    }
}
