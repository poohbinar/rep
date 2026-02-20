<?php

namespace App\Data;

use App\Enums\Deposit\SortDirections;
use App\Enums\Deposit\SortFields;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Symfony\Component\HttpFoundation\Response;

class DepositHistoryFilterData extends Data
{
    public const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    public const int DEFAULT_PER_PAGE = 10;

    public const int DEFAULT_PAGE_NUMBER = 1;

    public const SortFields DEFAULT_SORT_FIELD = SortFields::CREDITED_AT;

    public const SortDirections DEFAULT_SORT_DIRECTION = SortDirections::DESC_DIRECTION;

    #[Sometimes, WithCast(EnumCast::class), MapInputName('sort_field')]
    public SortFields $sortField = self::DEFAULT_SORT_FIELD;

    #[Sometimes, WithCast(EnumCast::class), MapInputName('sort')]
    public SortDirections $sort = self::DEFAULT_SORT_DIRECTION;

    #[Sometimes, IntegerType, Min(1), Max(100), MapInputName('per_page')]
    public int $perPage = self::DEFAULT_PER_PAGE;

    #[Sometimes, IntegerType, Min(1), MapInputName('page_number')]
    public int $pageNumber = self::DEFAULT_PAGE_NUMBER;

    #[Sometimes, DateFormat(self::DATETIME_FORMAT), MapInputName('date_from')]
    public ?string $dateFrom = null;

    #[Sometimes, DateFormat(self::DATETIME_FORMAT), MapInputName('date_to')]
    public ?string $dateTo = null;

    public static function from(mixed ...$payloads): static
    {
        $payload = $payloads[0] ?? [];

        if (! empty($payload['date_from']) && empty($payload['date_to'])) {
            $payload['date_to'] = now()->format(self::DATETIME_FORMAT);
        }

        return parent::from($payload);
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $validator->getData();

            if (! empty($data['date_from']) && ! empty($data['date_to']) && $data['date_to'] < $data['date_from']) {
                $validator->errors()->add(
                    'date_to',
                    'The date to field must be a date after or equal to date from.'
                );

                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'Validation failed.',
                        'errors' => $validator->errors()->toArray(),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY)
                );
            }
        });
    }
}
