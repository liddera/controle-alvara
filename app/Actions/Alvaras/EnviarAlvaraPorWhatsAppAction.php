<?php

namespace App\Actions\Alvaras;

use App\Models\Alvara;
use App\Models\Notificacao;
use App\Models\WhatsAppInstance;
use App\Services\Dispatch\DocumentDispatchService;
use App\Services\Dispatch\DispatchStatus;
use App\Services\DocumentoService;
use App\Services\WhatsApp\WhatsAppOutboxService;
use Illuminate\Support\Str;

class EnviarAlvaraPorWhatsAppAction
{
    public function __construct(
        private DocumentoService $documentoService,
        private WhatsAppOutboxService $outboxService,
        private DocumentDispatchService $documentDispatchService,
    ) {}

    /**
     * @param array $dados ['nome', 'telefone', 'mensagem']
     */
    public function execute(Alvara $alvara, array $dados): Notificacao
    {
        $ownerId = $alvara->owner_id;
        $toNumber = $this->normalizeNumber((string) ($dados['telefone'] ?? ''));

        if (! $toNumber) {
            throw new \InvalidArgumentException('Telefone invalido.');
        }

        $instance = WhatsAppInstance::query()->where('owner_id', $ownerId)->first();

        if (! $instance || $instance->status !== 'open') {
            throw new \RuntimeException('WhatsApp nao conectado para este cliente. Conecte em Configuracoes de Alerta.');
        }

        $introText = $this->buildIntroText($alvara, $dados);

        $dispatch = \App\Models\DocumentDispatch::create([
            'owner_id' => (int) $alvara->owner_id,
            'alvara_id' => (int) $alvara->getKey(),
            'requested_by_user_id' => auth()->id() ?? $alvara->user_id,
            'trigger_type' => 'manual',
            'channel' => 'whatsapp',
            'destination_name' => $dados['nome'] ?? null,
            'destination_phone' => $toNumber,
            'current_status' => DispatchStatus::SENDING,
            'status_rank' => DispatchStatus::rank(DispatchStatus::SENDING),
            'requested_at' => now(),
            'last_event_at' => now(),
            'summary_payload' => [
                'mensagem_personalizada' => $dados['mensagem'] ?? null,
            ],
        ]);

        $introMessage = \App\Models\DocumentDispatchMessage::create([
            'document_dispatch_id' => (int) $dispatch->getKey(),
            'owner_id' => (int) $alvara->owner_id,
            'provider' => 'whatsapp_gateway',
            'channel' => 'whatsapp',
            'message_type' => 'whatsapp_intro',
            'current_status' => DispatchStatus::SENDING,
            'status_rank' => DispatchStatus::rank(DispatchStatus::SENDING),
            'destination_phone' => $toNumber,
            'metadata' => [
                'alvara_id' => (int) $alvara->getKey(),
            ],
        ]);

        $this->outboxService->queueText(
            $ownerId,
            $instance->instance_key,
            $toNumber,
            $introText,
            $introMessage->getKey(),
        );

        $expiresAt = now()->addMinutes(10);
        $documentos = $alvara->documentos ?? collect();

        foreach ($documentos as $documento) {
            $fileUrl = $this->documentoService->getTemporaryUrl($documento, $expiresAt);
            $fileName = $documento->nome_arquivo ?: 'documento.pdf';
            $mimeType = $documento->tipo ?: 'application/octet-stream';

            $caption = "Documento: {$fileName}";

            $dispatchMessage = \App\Models\DocumentDispatchMessage::create([
                'document_dispatch_id' => (int) $dispatch->getKey(),
                'owner_id' => (int) $alvara->owner_id,
                'provider' => 'whatsapp_gateway',
                'channel' => 'whatsapp',
                'message_type' => 'whatsapp_document',
                'documento_id' => $documento->getKey(),
                'current_status' => DispatchStatus::SENDING,
                'status_rank' => DispatchStatus::rank(DispatchStatus::SENDING),
                'destination_phone' => $toNumber,
                'metadata' => [
                    'alvara_id' => (int) $alvara->getKey(),
                ],
            ]);

            $this->outboxService->queueDocumentByUrl(
                ownerId: $ownerId,
                instanceKey: $instance->instance_key,
                to: $toNumber,
                fileUrl: $fileUrl,
                fileName: $fileName,
                mimeType: $mimeType,
                caption: $caption,
                documentId: $documento->getKey(),
                dispatchMessageId: $dispatchMessage->getKey(),
            );
        }

        return Notificacao::create([
            'user_id' => auth()->id() ?? $alvara->user_id,
            'alvara_id' => $alvara->id,
            'tipo' => 'envio_documento',
            'mensagem' => json_encode([
                'destinatario_nome' => $dados['nome'] ?? 'Sem Nome',
                'destinatario_telefone' => $toNumber,
                'metodo' => 'whatsapp',
                'mensagem_personalizada' => $dados['mensagem'] ?? null,
                'quantidade_documentos' => is_countable($documentos) ? count($documentos) : null,
                'remetente_nome' => auth()->user()->name ?? 'Sistema',
                'dispatch_id' => $dispatch->getKey(),
                'dispatch_status' => $dispatch->current_status,
            ]),
            'lida' => true,
            'data_envio' => now(),
        ]);
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

    private function buildIntroText(Alvara $alvara, array $dados): string
    {
        $recipientName = trim((string) ($dados['nome'] ?? ''));
        $greeting = $recipientName ? "Olá, {$recipientName}!" : 'Olá!';

        $tipo = $alvara->tipoAlvara?->nome ?? $alvara->tipo;
        $empresa = $alvara->empresa?->nome ?? '';
        $dataVencimento = $alvara->data_vencimento?->format('d/m/Y') ?? null;

        $lines = [
            $greeting,
            "Segue o(s) documento(s) do alvará {$alvara->numero} ({$tipo}).",
            $empresa ? "Empresa: {$empresa}" : null,
            $dataVencimento ? "Vencimento: {$dataVencimento}" : null,
        ];

        $mensagem = trim((string) ($dados['mensagem'] ?? ''));

        if ($mensagem) {
            $lines[] = '';
            $lines[] = Str::limit($mensagem, 800);
        }

        return implode("\n", array_values(array_filter($lines, fn ($line) => $line !== null)));
    }
}
