<?php

namespace App\Services\Dispatch;

use App\Models\Alvara;
use App\Models\DocumentDispatchMessage;
use Illuminate\Support\Carbon;

class DispatchHistoryBuilder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildForAlvara(Alvara $alvara): array
    {
        $items = [];

        foreach ($alvara->documentDispatches as $dispatch) {
            foreach ($dispatch->messages as $message) {
                $destination = $dispatch->destination_name
                    ?: $message->destination_email
                    ?: $message->destination_phone
                    ?: $dispatch->destination_email
                    ?: $dispatch->destination_phone
                    ?: 'Desconhecido';

                $method = $message->channel ?: $dispatch->channel;

                $baseDate = $dispatch->requested_at ?: $dispatch->created_at ?: $message->created_at;

                if ($baseDate) {
                    $items[] = $this->makeItem(
                        date: $baseDate,
                        destination: $destination,
                        method: $method,
                        status: DispatchStatus::SENDING,
                    );
                }

                foreach ($message->events as $event) {
                    $status = $event->normalized_status ?: $message->current_status;
                    $eventDate = $event->occurred_at ?: $event->received_at ?: $event->created_at ?: $baseDate;

                    if (! $eventDate) {
                        continue;
                    }

                    $items[] = $this->makeItem(
                        date: $eventDate,
                        destination: $destination,
                        method: $method,
                        status: $status,
                    );
                }
            }
        }

        usort($items, function ($left, $right) {
            $leftTs = (int) ($left['ts'] ?? 0);
            $rightTs = (int) ($right['ts'] ?? 0);

            if ($leftTs !== $rightTs) {
                return $rightTs <=> $leftTs;
            }

            $leftRank = (int) ($left['status_rank'] ?? 0);
            $rightRank = (int) ($right['status_rank'] ?? 0);

            if ($leftRank !== $rightRank) {
                return $rightRank <=> $leftRank;
            }

            return 0;
        });

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeItem(Carbon $date, string $destination, string $method, ?string $status): array
    {
        $timezone = (string) config('app.timezone');
        $localized = $date->copy()->timezone($timezone);

        return [
            'data' => $localized->format('d/m/Y H:i'),
            'ts' => $localized->timestamp,
            'destinatario' => $destination,
            'metodo' => $method,
            'status' => $status,
            'status_rank' => DispatchStatus::rank($status),
        ];
    }
}
