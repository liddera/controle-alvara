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
        $user = User::query()->select(['id', 'email', 'owner_id'])->whereKey($userId)->first();
        $primaryEmail = $user?->email;
        $ownerId = $user?->owner_id ?: $userId;

        $recipientEmails = collect($dto->recipient_emails)
            ->filter(fn ($email) => filled($email))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->when($primaryEmail, fn ($emails) => $emails->reject(fn ($email) => $email === strtolower($primaryEmail)))
            ->unique()
            ->values()
            ->all();

        $recipientPhones = collect($dto->recipient_phones)
            ->filter(fn ($phone) => filled($phone))
            ->map(function ($phone) {
                $normalized = preg_replace('/\D+/', '', (string) $phone);

                if (str_starts_with($normalized, '00')) {
                    $normalized = substr($normalized, 2);
                }

                return $normalized;
            })
            ->filter(fn ($phone) => filled($phone))
            ->unique()
            ->values()
            ->all();

        $payload = $dto->toArray();
        $payload['recipient_emails'] = $recipientEmails;
        $payload['recipient_phones'] = $recipientPhones;

        return AlertConfig::updateOrCreate(
            [
                'owner_id' => $ownerId,
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
