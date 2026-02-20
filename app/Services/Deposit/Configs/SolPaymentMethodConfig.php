<?php

namespace App\Services\Deposit\Configs;

use App\Contracts\PaymentMethodConfigInterface;
use App\Enums\Deposit\PaymentMethodEnum;

class SolPaymentMethodConfig implements PaymentMethodConfigInterface
{
    private function cfg(): array
    {
        return config('deposit.sol');
    }

    public static function getName(): string
    {
        return PaymentMethodEnum::SOL->value;
    }

    public function minAmount(): float
    {
        return (float) $this->cfg()['min_amount'];
    }

    public function maxAmount(): float
    {
        return (float) $this->cfg()['max_amount'];
    }

    public function isEnabled(): bool
    {
        return (bool) $this->cfg()['enabled'];
    }

    public function isDevOnly(): bool
    {
        return (bool) $this->cfg()['dev_only'];
    }

    public function allowedCountries(): array
    {
        return $this->cfg()['allowed_countries'] ?? ['*'];
    }

    public function priority(): int
    {
        return (int) $this->cfg()['priority'];
    }

    public function title(): string
    {
        return (string) $this->cfg()['title'];
    }

    public function description(): ?string
    {
        return $this->cfg()['description'] ?? null;
    }

    public function meta(): array
    {
        return [
            'currency' => $this->cfg()['currency'],
            'blockchain' => $this->cfg()['blockchain'],
            'decimals' => $this->cfg()['decimals'],
        ];
    }
}
