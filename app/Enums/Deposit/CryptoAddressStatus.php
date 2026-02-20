<?php

namespace App\Enums\Deposit;

enum CryptoAddressStatus: int
{
    case NEW = 0;
    case USED = 1;
    case QUARANTINE = 2;
    case ARCHIVED = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
