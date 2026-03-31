<?php

namespace App\Services\Dispatch;

class EmailDispatchStatusMapper
{
    public function mapEvent(?string $event): ?string
    {
        if (! is_string($event) || trim($event) === '') {
            return null;
        }

        return match (strtolower(trim($event))) {
            'request', 'deferred' => DispatchStatus::SENT,
            'delivered' => DispatchStatus::DELIVERED,
            'opened', 'unique_opened', 'click', 'proxy_open' => DispatchStatus::OPENED,
            'blocked', 'hard_bounce', 'soft_bounce', 'error', 'invalid_email', 'spam' => DispatchStatus::FAILED,
            'unsubscribed' => null,
            default => null,
        };
    }

    public function rankForEvent(?string $event): int
    {
        return DispatchStatus::rank($this->mapEvent($event));
    }
}
