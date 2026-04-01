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
                $method = $message->channel ?: $dispatch->channel;
                $email = $message->destination_email ?: $dispatch->destination_email;
                $phone = $message->destination_phone ?: $dispatch->destination_phone;

                $destination = $this->resolveDestination($method, $dispatch->destination_name, $email, $phone);

                $baseDate = $dispatch->requested_at ?: $dispatch->created_at ?: $message->created_at;

                if ($baseDate) {
                    $items[] = $this->makeItem(
                        date: $baseDate,
                        destination: $destination,
                        method: $method,
                        status: DispatchStatus::SENDING,
                        email: $email,
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
                        email: $email,
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
    private function makeItem(
        Carbon $date,
        string $destination,
        string $method,
        ?string $status,
        ?string $email = null
    ): array
    {
        $timezone = (string) config('app.timezone');
        $localized = $date->copy()->timezone($timezone);

        return [
            'data' => $localized->format('d/m/Y H:i'),
            'ts' => $localized->timestamp,
            'destinatario' => $destination,
            'email' => $email,
            'metodo' => $method,
            'status' => $status,
            'status_rank' => DispatchStatus::rank($status),
        ];
    }

    private function resolveDestination(
        ?string $method,
        ?string $name,
        ?string $email,
        ?string $phone
    ): string {
        $normalizedMethod = strtolower((string) ($method ?? ''));

        if ($normalizedMethod === 'whatsapp' && filled($phone)) {
            return $phone;
        }

        return $name
            ?: $email
            ?: $phone
            ?: 'Desconhecido';
    }
}
