<?php

namespace App\Services\Dispatch;

class DispatchStatusAggregator
{
    /**
     * @param iterable<int, string|null> $statuses
     */
    public function aggregate(iterable $statuses): string
    {
        $normalized = [];

        foreach ($statuses as $status) {
            $value = DispatchStatus::normalize($status);

            if ($value !== null) {
                $normalized[] = $value;
            }
        }

        if ($normalized === []) {
            return DispatchStatus::SENDING;
        }

        $unique = array_values(array_unique($normalized));

        if (count($unique) === 1) {
            return $unique[0];
        }

        if (in_array(DispatchStatus::PARTIAL, $unique, true)) {
            return DispatchStatus::PARTIAL;
        }

        if (in_array(DispatchStatus::FAILED, $unique, true)) {
            $nonFailed = array_filter(
                $unique,
                fn (string $status) => $status !== DispatchStatus::FAILED
            );

            if ($nonFailed === []) {
                return DispatchStatus::FAILED;
            }

            return DispatchStatus::PARTIAL;
        }

        usort($unique, fn (string $left, string $right) => DispatchStatus::rank($right) <=> DispatchStatus::rank($left));

        return $unique[0];
    }
}
