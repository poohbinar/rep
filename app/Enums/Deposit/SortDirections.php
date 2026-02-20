<?php

namespace App\Enums\Deposit;

enum SortDirections: string
{
    case DESC_DIRECTION = 'desc';
    case ASC_DIRECTION = 'asc';

    public const string DEFAULT_VALUE = self::DESC_DIRECTION->value;

    public const array AVAILABLE_VALUES = [self::DESC_DIRECTION, self::ASC_DIRECTION];

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $field): bool
    {
        return in_array($field, self::values());
    }
}
