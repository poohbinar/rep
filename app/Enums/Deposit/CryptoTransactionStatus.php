<?php

namespace App\Enums\Deposit;

enum CryptoTransactionStatus: int
{
    case DETECTED = 0;
    case CONFIRMED = 1;
    case FINALIZED = 2;
    case FAILED = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::FINALIZED, self::FAILED], true);
    }
}
