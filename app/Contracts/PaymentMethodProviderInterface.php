<?php

namespace App\Contracts;

use App\Models\CryptoDeposit;
use App\Models\PaymentTarget;

interface PaymentMethodProviderInterface
{
    public static function getName(): string;

    public function validateAmount(float $amount): void;

    public function validateAvailability(): void;

    public function validateFields(array $fields): void;

    public function extractAmount(array $fields): float;

    // TODO сомнительно, что это нужно
    public function estimateNetworkFee(): float;

    public function getPaymentTarget(CryptoDeposit $deposit): PaymentTarget;

    public function buildPaymentInstruction(CryptoDeposit $deposit): ?string;
}
