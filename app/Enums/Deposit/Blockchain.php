<?php

namespace App\Enums\Deposit;

enum Blockchain: string
{
    case SOLANA = 'solana';
    case ETHEREUM = 'ethereum';
    case BITCOIN = 'bitcoin';
    case TRON = 'tron';
    case TON = 'ton';

    public const array AVAILABLE_VALUES = [
        self::SOLANA,
        self::ETHEREUM,
        self::BITCOIN,
    ];

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isUtxo(): bool
    {
        return match ($this) {
            self::BITCOIN => true,
            default => false,
        };
    }

    public function supportsMemo(): bool
    {
        return match ($this) {
            self::SOLANA, self::TRON, self::TON => true,
            default => false,
        };
    }
}
