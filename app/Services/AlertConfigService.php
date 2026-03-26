<?php

namespace App\Services;

use App\Models\AlertConfig;
use App\Models\User;
use App\DTOs\AlertConfigDTO;
use Illuminate\Support\Collection;

class AlertConfigService
{
    public function listarPorUsuario(int $userId): Collection
    {
        return AlertConfig::where('user_id', $userId)
            ->with(['tipoAlvara', 'user:id,email'])
            ->get();
    }

    public function salvar(int $userId, AlertConfigDTO $dto): AlertConfig
    {
        $primaryEmail = User::query()->whereKey($userId)->value('email');

        $recipientEmails = collect($dto->recipient_emails)
            ->filter(fn ($email) => filled($email))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->when($primaryEmail, fn ($emails) => $emails->reject(fn ($email) => $email === strtolower($primaryEmail)))
            ->unique()
            ->values()
            ->all();

        $payload = $dto->toArray();
        $payload['recipient_emails'] = $recipientEmails;

        return AlertConfig::updateOrCreate(
            [
                'user_id' => $userId,
                'tipo_alvara_id' => $dto->tipo_alvara_id,
                'days_before' => $dto->days_before,
            ],
            $payload
        );
    }

    public function excluir(AlertConfig $config): void
    {
        $config->delete();
    }
}
