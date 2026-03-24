<?php

namespace App\Actions\Alerts;

use App\Services\AlertConfigService;
use App\DTOs\AlertConfigDTO;
use App\Models\AlertConfig;
use Illuminate\Http\Request;

class UpsertAlertConfigAction
{
    public function __construct(private AlertConfigService $service) {}

    public function execute(Request $request): AlertConfig
    {
        $dto = AlertConfigDTO::fromRequest($request);
        return $this->service->salvar(auth()->id(), $dto);
    }
}
