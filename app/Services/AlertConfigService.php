<?php

namespace App\Services;

use App\Models\AlertConfig;
use App\DTOs\AlertConfigDTO;
use Illuminate\Support\Collection;

class AlertConfigService
{
    public function listarPorUsuario(int $userId): Collection
    {
        return AlertConfig::where('user_id', $userId)->with('tipoAlvara')->get();
    }

    public function salvar(int $userId, AlertConfigDTO $dto): AlertConfig
    {
        return AlertConfig::updateOrCreate(
            [
                'user_id' => $userId,
                'tipo_alvara_id' => $dto->tipo_alvara_id,
                'days_before' => $dto->days_before,
            ],
            $dto->toArray()
        );
    }

    public function excluir(AlertConfig $config): void
    {
        $config->delete();
    }
}
