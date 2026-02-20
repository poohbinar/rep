<?php

namespace App\Enums\Deposit;

enum AmlRiskLevel: int
{
    case CLEAN = 0;
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isBlocked(): bool
    {
        return $this === self::HIGH;
    }
}
