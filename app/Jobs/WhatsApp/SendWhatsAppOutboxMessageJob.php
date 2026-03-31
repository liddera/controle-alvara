<?php

namespace App\Jobs\WhatsApp;

use App\Contracts\WhatsApp\WhatsAppGateway;
use App\Models\Documento;
use App\Models\DocumentDispatchMessage;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppOutboxMessage;
use App\Services\Dispatch\DocumentDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendWhatsAppOutboxMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(private readonly int $outboxId) {}

    public function handle(WhatsAppGateway $gateway, DocumentDispatchService $documentDispatchService): void
    {
        $outbox = WhatsAppOutboxMessage::query()->find($this->outboxId);

        if (! $outbox || $outbox->status === WhatsAppOutboxMessage::STATUS_SENT) {
            return;
        }

        $instance = WhatsAppInstance::query()
            ->where('owner_id', $outbox->owner_id)
            ->where('instance_key', $outbox->instance_key)
            ->first();

        if (! $instance) {
            throw new \RuntimeException('Instancia WhatsApp nao encontrada.');
        }

        try {
            $result = match ($outbox->type) {
                'text' => $gateway->sendText(
                    $outbox->instance_key,
                    $outbox->to,
                    (string) ($outbox->payload['text'] ?? ''),
                    $instance->instance_api_key,
                ),
                'document' => $this->sendDocument($gateway, $instance, $outbox),
                default => throw new \RuntimeException("Tipo de outbox nao suportado: {$outbox->type}"),
            };

            $outbox->provider_message_id = $result->messageId;
            $outbox->status = WhatsAppOutboxMessage::STATUS_SENT;
            $outbox->attempts = max((int) $outbox->attempts, (int) $this->attempts());
            $outbox->sent_at = now();
            $outbox->last_error = null;
            $outbox->save();

            $this->syncDispatchMessageStatus($outbox, $documentDispatchService);
        } catch (\Throwable $exception) {
            $outbox->status = WhatsAppOutboxMessage::STATUS_FAILED;
            $outbox->attempts = max((int) $outbox->attempts, (int) $this->attempts());
            $outbox->last_error = $exception->getMessage();
            $outbox->save();

            $this->syncDispatchMessageFailure($outbox, $documentDispatchService, $exception);

            throw $exception;
        }
    }

    private function sendDocument(WhatsAppGateway $gateway, WhatsAppInstance $instance, WhatsAppOutboxMessage $outbox): mixed
    {
        $mediaMode = strtolower((string) (config('services.whatsapp_gateway.media_mode') ?? 'auto'));

        $fileUrl = (string) ($outbox->payload['file_url'] ?? '');
        $fileName = (string) ($outbox->payload['file_name'] ?? 'documento');
        $mimeType = (string) ($outbox->payload['mime_type'] ?? 'application/octet-stream');
        $caption = $outbox->payload['caption'] ?? null;

        $shouldUseBase64 = match ($mediaMode) {
            'base64' => true,
            'url' => false,
            default => $this->shouldPreferBase64($fileUrl),
        };

        if (! $shouldUseBase64) {
            return $gateway->sendDocumentByUrl(
                $outbox->instance_key,
                $outbox->to,
                $fileUrl,
                $fileName,
                $mimeType,
                is_string($caption) ? $caption : null,
                $instance->instance_api_key,
            );
        }

        $documentId = $outbox->payload['document_id'] ?? null;
        $documento = null;

        if (is_numeric($documentId)) {
            $documento = Documento::query()->find((int) $documentId);
        }

        if (! $documento) {
            throw new \RuntimeException('Documento nao encontrado para envio em base64.');
        }

        $disk = config('filesystems.default');
        $contents = Storage::disk($disk)->get($documento->caminho);
        $base64 = base64_encode($contents);

        return $gateway->sendDocumentByBase64(
            $outbox->instance_key,
            $outbox->to,
            $base64,
            $fileName,
            $mimeType,
            is_string($caption) ? $caption : null,
            $instance->instance_api_key,
        );
    }

    private function shouldPreferBase64(string $fileUrl): bool
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (in_array($appHost, ['localhost', '127.0.0.1'], true)) {
            return true;
        }

        $host = parse_url($fileUrl, PHP_URL_HOST);

        return in_array($host, ['localhost', '127.0.0.1'], true);
    }

    private function syncDispatchMessageStatus(WhatsAppOutboxMessage $outbox, DocumentDispatchService $documentDispatchService): void
    {
        $dispatchMessageId = $outbox->payload['dispatch_message_id'] ?? null;

        if (! is_numeric($dispatchMessageId)) {
            return;
        }

        $dispatchMessage = DocumentDispatchMessage::query()->find((int) $dispatchMessageId);

        if (! $dispatchMessage) {
            return;
        }

        $documentDispatchService->updateMessageStatus(
            message: $dispatchMessage,
            status: \App\Services\Dispatch\DispatchStatus::SENT,
            providerStatusRaw: 'send_message',
            metadata: [
                'outbox_id' => $outbox->getKey(),
                'provider_message_id' => $outbox->provider_message_id,
            ],
            providerMessageId: $outbox->provider_message_id,
        );
    }

    private function syncDispatchMessageFailure(
        WhatsAppOutboxMessage $outbox,
        DocumentDispatchService $documentDispatchService,
        \Throwable $exception
    ): void {
        $dispatchMessageId = $outbox->payload['dispatch_message_id'] ?? null;

        if (! is_numeric($dispatchMessageId)) {
            return;
        }

        $dispatchMessage = DocumentDispatchMessage::query()->find((int) $dispatchMessageId);

        if (! $dispatchMessage) {
            return;
        }

        $documentDispatchService->updateMessageStatus(
            message: $dispatchMessage,
            status: \App\Services\Dispatch\DispatchStatus::FAILED,
            providerStatusRaw: 'failed',
            metadata: [
                'outbox_id' => $outbox->getKey(),
                'error' => $exception->getMessage(),
            ],
        );
    }
}
