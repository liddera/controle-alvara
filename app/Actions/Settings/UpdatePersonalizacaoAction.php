<?php

namespace App\Actions\Settings;

use App\DTOs\PersonalizacaoDTO;
use App\Models\Personalizacao;
use App\Services\PersonalizacaoService;
use Illuminate\Http\Request;

class UpdatePersonalizacaoAction
{
    public function __construct(private PersonalizacaoService $service) {}

    public function execute(Request $request): Personalizacao
    {
        $dto = PersonalizacaoDTO::fromRequest($request);
        return $this->service->salvar($dto);
    }
}
