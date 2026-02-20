<?php

namespace App\Enums\Deposit;

enum PaymentMethodEnum: string
{
    case BTC = 'btc';

    case SOL = 'sol';

    case ETH = 'eth';

    public const array AVAILABLE_VALUES = [
        self::SOL,
        self::BTC,
        self::ETH,
    ];
}
