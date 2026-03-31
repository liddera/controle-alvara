<?php

namespace Tests\Unit;

use App\Services\Dispatch\EmailDispatchStatusMapper;
use App\Services\Dispatch\DispatchStatus;
use App\Services\Dispatch\DispatchStatusAggregator;
use App\Services\Dispatch\WhatsAppDispatchStatusMapper;
use PHPUnit\Framework\TestCase;

class DispatchStatusTest extends TestCase
{
    public function test_dispatch_status_rank_order_is_consistent(): void
    {
        $this->assertGreaterThan(
            DispatchStatus::rank(DispatchStatus::SENDING),
            DispatchStatus::rank(DispatchStatus::SENT)
        );

        $this->assertGreaterThan(
            DispatchStatus::rank(DispatchStatus::SENT),
            DispatchStatus::rank(DispatchStatus::DELIVERED)
        );

        $this->assertGreaterThan(
            DispatchStatus::rank(DispatchStatus::DELIVERED),
            DispatchStatus::rank(DispatchStatus::OPENED)
        );
    }

    public function test_email_mapper_maps_main_events_to_unified_statuses(): void
    {
        $mapper = new EmailDispatchStatusMapper;

        $this->assertSame(DispatchStatus::SENT, $mapper->mapEvent('request'));
        $this->assertSame(DispatchStatus::SENT, $mapper->mapEvent('deferred'));
        $this->assertSame(DispatchStatus::DELIVERED, $mapper->mapEvent('delivered'));
        $this->assertSame(DispatchStatus::OPENED, $mapper->mapEvent('opened'));
        $this->assertSame(DispatchStatus::OPENED, $mapper->mapEvent('click'));
        $this->assertSame(DispatchStatus::FAILED, $mapper->mapEvent('hard_bounce'));
        $this->assertNull($mapper->mapEvent('unsubscribed'));
    }

    public function test_whatsapp_mapper_maps_events_and_provider_statuses(): void
    {
        $mapper = new WhatsAppDispatchStatusMapper;

        $this->assertSame(DispatchStatus::SENT, $mapper->mapProviderStatus('PENDING'));
        $this->assertSame(DispatchStatus::SENT, $mapper->mapEvent('SEND_MESSAGE'));
        $this->assertSame(DispatchStatus::DELIVERED, $mapper->mapEvent('MESSAGES_UPDATE', 'DELIVERY_ACK'));
        $this->assertSame(DispatchStatus::OPENED, $mapper->mapEvent('MESSAGES_UPDATE', 'READ'));
        $this->assertSame(DispatchStatus::FAILED, $mapper->mapProviderStatus('FAILED'));
        $this->assertNull($mapper->mapEvent('CONNECTION_UPDATE', 'open'));
    }

    public function test_aggregator_returns_most_advanced_status_when_all_messages_progress(): void
    {
        $aggregator = new DispatchStatusAggregator;

        $this->assertSame(
            DispatchStatus::OPENED,
            $aggregator->aggregate([
                DispatchStatus::SENT,
                DispatchStatus::DELIVERED,
                DispatchStatus::OPENED,
            ])
        );
    }

    public function test_aggregator_returns_partial_when_progress_and_failures_are_mixed(): void
    {
        $aggregator = new DispatchStatusAggregator;

        $this->assertSame(
            DispatchStatus::PARTIAL,
            $aggregator->aggregate([
                DispatchStatus::DELIVERED,
                DispatchStatus::FAILED,
            ])
        );
    }

    public function test_aggregator_returns_failed_when_everything_failed(): void
    {
        $aggregator = new DispatchStatusAggregator;

        $this->assertSame(
            DispatchStatus::FAILED,
            $aggregator->aggregate([
                DispatchStatus::FAILED,
                DispatchStatus::FAILED,
            ])
        );
    }
}
