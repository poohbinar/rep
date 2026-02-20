<?php

namespace Tests\Stubs;

use App\Contracts\PaymentMethodProviderInterface;
use App\Enums\Deposit\PaymentMethodEnum;
use App\Models\CryptoDeposit;
use App\Models\PaymentTarget;

class TestBtcProvider implements PaymentMethodProviderInterface
{
    public static function getName(): string
    {
        return PaymentMethodEnum::BTC->value;
    }

    public function validateAvailability(): void
    {
        // no-op для тестов
    }

    public function validateAmount(float $amount): void
    {
        // no-op для тестов
    }

    /**
     * В тестах просто проверяем что amount присутствует
     */
    public function validateFields(array $fields): void
    {
        if (! array_key_exists('amount', $fields)) {
            throw new \InvalidArgumentException('Amount field is required');
        }
    }

    public function extractAmount(array $fields): float
    {
        return (float) ($fields['amount'] ?? 0);
    }

    public function getPaymentTarget(CryptoDeposit $deposit): PaymentTarget
    {
        throw new \LogicException('Not needed for this test');
    }

    public function estimateNetworkFee(): float
    {
        return 0.0;
    }

    public function buildPaymentInstruction(CryptoDeposit $deposit): ?string
    {
        return 'bitcoin:test-address?amount=' . $deposit->expected_crypto_amount;
    }
}
