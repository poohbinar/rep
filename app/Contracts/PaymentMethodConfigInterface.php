<?php

namespace App\Contracts;

interface PaymentMethodConfigInterface
{
    public static function getName(): string;

    public function minAmount(): float;

    public function maxAmount(): float;

    public function isEnabled(): bool;

    public function isDevOnly(): bool;

    public function allowedCountries(): array;

    public function priority(): int;

    public function title(): string;

    public function description(): ?string;

    public function meta(): array;
}
