<?php

namespace App\Services\WhatsApp;

class WhatsAppStatusPresenter
{
    /**
     * @return array{label: string, class: string, status: string}
     */
    public function present(?string $status): array
    {
        $normalized = is_string($status) ? strtolower(trim($status)) : '';

        return match ($normalized) {
            OwnerWhatsAppInstanceService::STATUS_CONNECTED => [
                'label' => 'Conectado',
                'class' => 'bg-green-100 text-green-700',
                'status' => $normalized,
            ],
            OwnerWhatsAppInstanceService::STATUS_CONNECTING => [
                'label' => 'Aguardando conexao',
                'class' => 'bg-amber-100 text-amber-700',
                'status' => $normalized,
            ],
            OwnerWhatsAppInstanceService::STATUS_MISCONFIGURED => [
                'label' => 'Indisponivel',
                'class' => 'bg-gray-100 text-gray-700',
                'status' => $normalized,
            ],
            default => [
                'label' => 'Desconectado',
                'class' => 'bg-red-100 text-red-700',
                'status' => $normalized ?: OwnerWhatsAppInstanceService::STATUS_DISCONNECTED,
            ],
        };
    }
}
