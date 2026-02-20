<?php

namespace App\Enums\Deposit;

enum SortFields: string
{
    case CREDITED_AT = 'credited_at';

    case CREATED_AT = 'created_at';

    public const string DEFAULT_VALUE = self::CREDITED_AT->value;

    public const array AVAILABLE_VALUES = [
        self::CREDITED_AT->value,
        self::CREATED_AT->value,
    ];

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $field): bool
    {
        return in_array($field, self::values());
    }
}
