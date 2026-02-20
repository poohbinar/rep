<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class CreateDepositData extends Data
{
    public function __construct(
        #[Required, StringType, MapInputName('payment_method')]
        public string $paymentMethod,

        #[Required]
        public array $fields,

        #[Sometimes, StringType, MapInputName('promo_code')]
        public ?string $promoCode = null
    ) {}
}
