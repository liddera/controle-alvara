<?php

namespace App\Services\Dispatch;

use App\Models\Alvara;
use App\Models\DocumentDispatch;
use App\Models\DocumentDispatchMessage;

class DocumentDispatchService
{
    /**
     * @return array{0: DocumentDispatch, 1: DocumentDispatchMessage}
     */
    public function createEmailDispatch(Alvara $alvara, array $dados, ?int $requestedByUserId = null, ?int $notificacaoId = null): array
    {
        $dispatch = DocumentDispatch::create([
            'owner_id' => (int) $alvara->owner_id,
            'alvara_id' => (int) $alvara->getKey(),
            'requested_by_user_id' => $requestedByUserId,
            'trigger_type' => 'manual',
            'channel' => 'email',
            'destination_name' => $dados['nome'] ?? null,
            'destination_email' => $dados['email'] ?? null,
            'destination_phone' => $dados['telefone'] ?? null,
            'current_status' => DispatchStatus::SENDING,
            'status_rank' => DispatchStatus::rank(DispatchStatus::SENDING),
            'requested_at' => now(),
            'last_event_at' => now(),
            'summary_payload' => [
                'whatsapp_aviso' => (bool) ($dados['enviar_aviso_whatsapp'] ?? false),
                'mensagem_personalizada' => $dados['mensagem'] ?? null,
                'notificacao_id' => $notificacaoId,
            ],
        ]);

        $message = DocumentDispatchMessage::create([
            'document_dispatch_id' => (int) $dispatch->getKey(),
            'owner_id' => (int) $alvara->owner_id,
            'provider' => 'email_provider',
            'channel' => 'email',
            'message_type' => 'email_document_bundle',
            'current_status' => DispatchStatus::SENDING,
            'status_rank' => DispatchStatus::rank(DispatchStatus::SENDING),
            'destination_email' => $dados['email'] ?? null,
            'metadata' => [
                'alvara_id' => (int) $alvara->getKey(),
                'document_count' => $alvara->documentos->count(),
            ],
        ]);

        return [$dispatch, $message];
    }

    public function markMessageAsSent(
        DocumentDispatchMessage $message,
        ?string $providerMessageId,
        ?string $providerStatusRaw = null,
        array $metadata = [],
    ): void {
        $this->updateMessageStatus(
            message: $message,
            status: DispatchStatus::SENT,
            providerStatusRaw: $providerStatusRaw,
            metadata: array_merge($metadata, ['provider_message_id' => $providerMessageId]),
            providerMessageId: $providerMessageId,
        );
    }

    public function markMessageAsFailed(
        DocumentDispatchMessage $message,
        ?string $providerStatusRaw = null,
        array $metadata = [],
    ): void {
        $this->updateMessageStatus(
            message: $message,
            status: DispatchStatus::FAILED,
            providerStatusRaw: $providerStatusRaw,
            metadata: $metadata
        );
    }

    public function updateMessageStatus(
        DocumentDispatchMessage $message,
        string $status,
        ?string $providerStatusRaw = null,
        array $metadata = [],
        ?string $providerMessageId = null,
    ): void {
        $payload = [
            'provider_status_raw' => $providerStatusRaw,
            'current_status' => $status,
            'status_rank' => DispatchStatus::rank($status),
            'metadata' => array_merge($message->metadata ?? [], $metadata),
        ];

        if ($providerMessageId) {
            $payload['provider_message_id'] = $providerMessageId;
        }

        if ($status === DispatchStatus::SENT && ! $message->sent_at) {
            $payload['sent_at'] = now();
        }

        if ($status === DispatchStatus::DELIVERED && ! $message->delivered_at) {
            $payload['delivered_at'] = now();
        }

        if ($status === DispatchStatus::OPENED && ! $message->opened_at) {
            $payload['opened_at'] = now();
        }

        if ($status === DispatchStatus::FAILED && ! $message->failed_at) {
            $payload['failed_at'] = now();
        }

        $message->forceFill($payload)->save();

        $this->refreshParentDispatch($message);
    }

    public function refreshParentDispatch(DocumentDispatchMessage $message): void
    {
        $message->loadMissing('dispatch.messages');

        $dispatch = $message->dispatch;

        if (! $dispatch) {
            return;
        }

        $status = app(DispatchStatusAggregator::class)->aggregate(
            $dispatch->messages->pluck('current_status')->all()
        );

        $dispatch->forceFill([
            'current_status' => $status,
            'status_rank' => DispatchStatus::rank($status),
            'last_event_at' => now(),
        ])->save();
    }
}
