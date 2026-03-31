<?php

namespace App\Services\WhatsApp;

use App\Models\Alvara;

class WhatsAppAlertMessageFactory
{
    public function makeVencimentoMessage(Alvara $alvara, int $daysBefore): string
    {
        $statusLabel = $daysBefore > 0
            ? "vence em {$daysBefore} dias"
            : 'vence HOJE';

        $tipo = $alvara->tipoAlvara?->nome ?? $alvara->tipo;
        $empresa = $alvara->empresa?->nome ?? 'Empresa';
        $dataVencimento = $alvara->data_vencimento?->format('d/m/Y') ?? '';

        $lines = [
            "Alerta de vencimento: {$tipo}",
            "Numero: {$alvara->numero}",
            "Empresa: {$empresa}",
            $dataVencimento ? "Data de vencimento: {$dataVencimento} ({$statusLabel})" : "Status: {$statusLabel}",
        ];

        return implode("\n", array_filter($lines));
    }
}

