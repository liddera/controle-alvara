<?php

namespace App\Services\WhatsApp;

use App\Jobs\WhatsApp\SendWhatsAppOutboxMessageJob;
use App\Models\WhatsAppOutboxMessage;

class WhatsAppOutboxService
{
    public function queueText(
        int $ownerId,
        string $instanceKey,
        string $to,
        string $text,
        ?int $dispatchMessageId = null
    ): WhatsAppOutboxMessage
    {
        $outbox = WhatsAppOutboxMessage::create([
            'owner_id' => $ownerId,
            'instance_key' => $instanceKey,
            'type' => 'text',
            'to' => $to,
            'payload' => [
                'text' => $text,
                'dispatch_message_id' => $dispatchMessageId,
            ],
            'status' => WhatsAppOutboxMessage::STATUS_QUEUED,
        ]);

        SendWhatsAppOutboxMessageJob::dispatch($outbox->getKey());

        return $outbox;
    }

    public function queueDocumentByUrl(
        int $ownerId,
        string $instanceKey,
        string $to,
        string $fileUrl,
        string $fileName,
        string $mimeType,
        ?string $caption = null,
        ?int $documentId = null,
        ?int $dispatchMessageId = null,
    ): WhatsAppOutboxMessage {
        $outbox = WhatsAppOutboxMessage::create([
            'owner_id' => $ownerId,
            'instance_key' => $instanceKey,
            'type' => 'document',
            'to' => $to,
            'payload' => [
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'caption' => $caption,
                'document_id' => $documentId,
                'dispatch_message_id' => $dispatchMessageId,
            ],
            'status' => WhatsAppOutboxMessage::STATUS_QUEUED,
        ]);

        SendWhatsAppOutboxMessageJob::dispatch($outbox->getKey());

        return $outbox;
    }
}
