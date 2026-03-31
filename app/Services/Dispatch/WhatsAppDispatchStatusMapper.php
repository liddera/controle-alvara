<?php

namespace App\Services\Dispatch;

class WhatsAppDispatchStatusMapper
{
    public function mapProviderStatus(?string $status): ?string
    {
        if (! is_string($status) || trim($status) === '') {
            return null;
        }

        return match (strtoupper(trim($status))) {
            'PENDING', 'SEND_MESSAGE', 'SERVER_ACK', 'SENT' => DispatchStatus::SENT,
            'DELIVERY_ACK', 'DELIVERED' => DispatchStatus::DELIVERED,
            'READ', 'READ_MESSAGES' => DispatchStatus::OPENED,
            'ERROR', 'FAILED' => DispatchStatus::FAILED,
            default => null,
        };
    }

    public function mapEvent(?string $event, ?string $messageStatus = null): ?string
    {
        $eventNormalized = is_string($event) ? strtoupper(trim($event)) : null;

        if ($eventNormalized === 'MESSAGES_UPDATE') {
            return $this->mapProviderStatus($messageStatus);
        }

        if ($eventNormalized === 'SEND_MESSAGE') {
            return DispatchStatus::SENT;
        }

        return $this->mapProviderStatus($messageStatus);
    }

    public function rankForEvent(?string $event, ?string $messageStatus = null): int
    {
        return DispatchStatus::rank($this->mapEvent($event, $messageStatus));
    }
}
