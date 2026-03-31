<?php

namespace App\Jobs\Alvaras;

use App\Models\Alvara;
use App\Models\DocumentDispatch;
use App\Models\DocumentDispatchMessage;
use App\Models\WhatsAppInstance;
use App\Services\Dispatch\DispatchStatus;
use App\Services\Dispatch\DocumentDispatchService;
use App\Services\Email\TransactionalEmailService;
use App\Services\WhatsApp\WhatsAppOutboxService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendAlvaraEmailAndNotifyWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    /**
     * @param array $dados ['nome', 'email', 'telefone', 'mensagem', 'enviar_aviso_whatsapp']
     */
    public function __construct(
        private readonly int $alvaraId,
        private readonly array $dados,
        private readonly int $ownerId,
        private readonly int $dispatchId,
        private readonly int $dispatchMessageId,
    ) {}

    public function handle(
        WhatsAppOutboxService $outboxService,
        TransactionalEmailService $transactionalEmailService,
        DocumentDispatchService $documentDispatchService,
    ): void
    {
        $alvara = Alvara::query()
            ->with(['empresa', 'tipoAlvara', 'documentos'])
            ->findOrFail($this->alvaraId);

        $dispatch = DocumentDispatch::query()->findOrFail($this->dispatchId);
        $dispatchMessage = DocumentDispatchMessage::query()->findOrFail($this->dispatchMessageId);
        $email = (string) ($this->dados['email'] ?? '');

        try {
            $result = $transactionalEmailService->sendAlvaraEmail(
                alvara: $alvara,
                dados: $this->dados,
                dispatch: $dispatch,
                message: $dispatchMessage,
            );

            $documentDispatchService->markMessageAsSent(
                message: $dispatchMessage,
                providerMessageId: $result->messageId,
                providerStatusRaw: 'request',
                metadata: [
                    'provider_response' => $result->raw,
                ],
            );
        } catch (\Throwable $exception) {
            $documentDispatchService->markMessageAsFailed(
                message: $dispatchMessage,
                providerStatusRaw: 'error',
                metadata: [
                    'error' => $exception->getMessage(),
                ],
            );

            throw $exception;
        }

        // 2) Opcional: aviso no WhatsApp (apenas texto).
        if (! (bool) ($this->dados['enviar_aviso_whatsapp'] ?? false)) {
            return;
        }

        $toNumber = $this->normalizeNumber((string) ($this->dados['telefone'] ?? ''));

        if (! $toNumber) {
            $fallback = $this->normalizeNumber((string) ($alvara->empresa?->telefone ?? ''));
            $toNumber = $fallback;
        }

        if (! $toNumber) {
            Log::info('Aviso WhatsApp ignorado: telefone ausente/invalidado.', [
                'owner_id' => $this->ownerId,
                'alvara_id' => $this->alvaraId,
            ]);
            return;
        }

        $instance = WhatsAppInstance::query()
            ->where('owner_id', $this->ownerId)
            ->first();

        if (! $instance || $instance->status !== 'open') {
            Log::info('Aviso WhatsApp ignorado: instancia nao conectada.', [
                'owner_id' => $this->ownerId,
                'alvara_id' => $this->alvaraId,
                'instance_status' => $instance?->status,
            ]);
            return;
        }

        try {
            $whatsDispatchMessage = DocumentDispatchMessage::query()->firstOrCreate(
                [
                    'document_dispatch_id' => (int) $dispatch->getKey(),
                    'provider' => 'whatsapp_gateway',
                    'channel' => 'whatsapp',
                    'message_type' => 'whatsapp_notice',
                    'destination_phone' => $toNumber,
                ],
                [
                    'owner_id' => (int) $this->ownerId,
                    'current_status' => DispatchStatus::SENDING,
                    'status_rank' => DispatchStatus::rank(DispatchStatus::SENDING),
                    'metadata' => [
                        'alvara_id' => (int) $alvara->getKey(),
                        'origin' => 'email_notice',
                    ],
                ]
            );

            $outboxService->queueText(
                ownerId: $this->ownerId,
                instanceKey: $instance->instance_key,
                to: $toNumber,
                text: $this->buildNoticeText($alvara, $email),
                dispatchMessageId: (int) $whatsDispatchMessage->getKey(),
            );
        } catch (\Throwable $exception) {
            Log::warning('Falha ao enfileirar aviso WhatsApp (nao bloqueia o e-mail).', [
                'owner_id' => $this->ownerId,
                'alvara_id' => $this->alvaraId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizeNumber(string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', $input);

        if (! filled($digits)) {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    private function buildNoticeText(Alvara $alvara, string $email): string
    {
        $recipientName = trim((string) ($this->dados['nome'] ?? ''));
        $greeting = $recipientName ? "Olá, {$recipientName}!" : 'Olá!';

        $tipo = $alvara->tipoAlvara?->nome ?? $alvara->tipo;
        $empresa = $alvara->empresa?->nome ?? '';

        $lines = [
            $greeting,
            'Seu alvará foi enviado por e-mail.',
            $alvara->numero ? "Alvará: {$alvara->numero} ({$tipo})" : "Alvará: {$tipo}",
            $empresa ? "Empresa: {$empresa}" : null,
            "E-mail: {$email}",
            'Verifique também a caixa de spam, se necessário.',
        ];

        $text = implode("\n", array_values(array_filter($lines, fn ($line) => $line !== null)));

        return Str::limit($text, 900);
    }
}
