<?php

namespace App\Enums\Deposit;

enum DepositStatuses: int
{
    case NEW = 0;               // создан, но адрес не показан
    case AWAITING_PAYMENT = 1;  // адрес выдан, ждём tx
    case TX_DETECTED = 2;       // tx есть, AML / confirmations
    case CREDITED = 3;          // зачислен в wallet
    case REJECTED = 4;          // отклонён

    public const int DEFAULT_VALUE = self::NEW->value;

    public const array AVAILABLE_VALUES = [
        self::NEW,
        self::AWAITING_PAYMENT,
        self::TX_DETECTED,
        self::CREDITED,
        self::REJECTED,
    ];

    public const array AVAILABLE_API_VALUES = [
        'new',
        'awaiting_payment',
        'tx_detected',
        'credited',
        'rejected',
    ];

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $field): bool
    {
        return in_array($field, self::values());
    }

    public function label(): string
    {
        return strtolower($this->name);
    }

    public function api(): string
    {
        return match ($this) {
            self::NEW => 'new',
            self::AWAITING_PAYMENT => 'awaiting_payment',
            self::TX_DETECTED => 'tx_detected',
            self::CREDITED => 'credited',
            self::REJECTED => 'rejected',
        };
    }

    public static function fromApi(string $status): self
    {
        return match ($status) {
            'new' => self::NEW,
            'awaiting_payment' => self::AWAITING_PAYMENT,
            'tx_detected' => self::TX_DETECTED,
            'credited' => self::CREDITED,
            'rejected' => self::REJECTED,
            default => throw new \InvalidArgumentException("Invalid deposit status: {$status}"),
        };
    }

    public static function apiValues(): array
    {
        return array_map(
            fn (self $case) => $case->api(),
            self::cases()
        );
    }

    public static function dbValues(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            self::cases()
        );
    }
}
