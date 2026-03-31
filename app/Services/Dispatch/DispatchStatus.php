<?php

namespace App\Services\Dispatch;

class DispatchStatus
{
    public const SENDING = 'enviando';
    public const SENT = 'enviado';
    public const DELIVERED = 'recebido';
    public const OPENED = 'aberto';
    public const FAILED = 'falhou';
    public const PARTIAL = 'parcial';

    /**
     * @return array<string, int>
     */
    public static function ranks(): array
    {
        return [
            self::SENDING => 10,
            self::SENT => 20,
            self::DELIVERED => 30,
            self::OPENED => 40,
            self::FAILED => 50,
            self::PARTIAL => 60,
        ];
    }

    public static function rank(?string $status): int
    {
        $normalized = self::normalize($status);

        if ($normalized === null) {
            return 0;
        }

        return self::ranks()[$normalized] ?? 0;
    }

    public static function normalize(?string $status): ?string
    {
        if (! is_string($status)) {
            return null;
        }

        $normalized = strtolower(trim($status));

        if ($normalized === '') {
            return null;
        }

        return array_key_exists($normalized, self::ranks()) ? $normalized : null;
    }

    public static function isTerminal(?string $status): bool
    {
        $normalized = self::normalize($status);

        return in_array($normalized, [self::OPENED, self::FAILED, self::PARTIAL], true);
    }
}
