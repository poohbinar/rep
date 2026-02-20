<?php

namespace Tests\Fakes;

use App\Contracts\PaymentMethodConfigInterface;

final class FakeCountryRestrictedPaymentMethodConfig implements PaymentMethodConfigInterface
{
    public static function getName(): string
    {
        return 'sol';
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function isDevOnly(): bool
    {
        return false;
    }

    public function allowedCountries(): array
    {
        return ['NL'];
    }

    public function title(): string
    {
        return 'SOL';
    }

    public function description(): string
    {
        return '';
    }

    public function minAmount(): float
    {
        return 1;
    }

    public function maxAmount(): float
    {
        return 1000;
    }

    public function priority(): int
    {
        return 1;
    }

    public function meta(): array
    {
        return [
            'currency' => 'SOL',
            'blockchain' => 'solana',
            'decimals' => 9,
        ];
    }
}
