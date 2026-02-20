<?php

namespace App\Enums\Deposit;

enum PaymentTargetType: string
{
    case ADDRESS = 'address'; // уникальный адрес
    case BUFFER = 'buffer';  // общий кошелёк
    case INVOICE = 'invoice'; // внешний провайдер

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
