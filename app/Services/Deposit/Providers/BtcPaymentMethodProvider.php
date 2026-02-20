<?php

namespace App\Services\Deposit\Providers;

use App\Contracts\PaymentMethodConfigInterface;
use App\Contracts\PaymentMethodProviderInterface;
use App\Enums\Deposit\PaymentMethodEnum;
use App\Exceptions\Deposit\InvalidDepositAmountException;
use App\Models\CryptoDeposit;
use App\Models\PaymentTarget;
use App\Services\CryptoAddressService;

class BtcPaymentMethodProvider implements PaymentMethodProviderInterface
{
    public function __construct(
        private readonly PaymentMethodConfigInterface $config,
        private readonly CryptoAddressService $cryptoAddressService,
    ) {}

    public static function getName(): string
    {
        return PaymentMethodEnum::BTC->value;
    }

    public function validateAvailability(): void
    {
        if (! $this->config->isEnabled()) {
            throw new InvalidDepositAmountException('Payment method is disabled.');
        }

        if ($this->config->isDevOnly() && ! app()->environment('local')) {
            throw new InvalidDepositAmountException('Payment method is dev only.');
        }
    }

    public function validateAmount(float $amount): void
    {
        if ($amount < $this->config->minAmount()) {
            throw new InvalidDepositAmountException(
                "Amount is less than minimum ({$this->config->minAmount()})"
            );
        }

        if ($amount > $this->config->maxAmount()) {
            throw new InvalidDepositAmountException(
                "Amount exceeds maximum ({$this->config->maxAmount()})"
            );
        }
    }

    /**
     * Создаёт payment_target для BTC (уникальный адрес)
     */
    public function getPaymentTarget(CryptoDeposit $deposit): PaymentTarget
    {
        $address = $this->cryptoAddressService->allocateAddress(
            blockchain: 'bitcoin'
        );

        $paymentTarget = PaymentTarget::create([
            'crypto_deposit_id' => $deposit->id,
            'type' => 'address',
            'blockchain' => 'bitcoin',
            'address' => $address->address,
            'memo' => null,
            'expires_at' => now()->addHours(1),
        ]);

        $address->update([
            'payment_target_id' => $paymentTarget->id,
        ]);

        return $paymentTarget;
    }

    public function estimateNetworkFee(): float
    {
        // BTC fee динамический, здесь — conservative estimate
        return 0.00005;
    }

    public function validateFields(array $fields): void
    {
        if (! isset($fields['amount'])) {
            throw new InvalidDepositAmountException('Amount is required');
        }
    }

    public function extractAmount(array $fields): float
    {
        return (float) $fields['amount'];
    }

    public function buildPaymentInstruction(CryptoDeposit $deposit): ?string
    {
        $target = $deposit->paymentTarget;

        if (! $target) {
            return null;
        }

        // BIP-21 URI
        // bitcoin:<address>?amount=<btc>
        return sprintf(
            'bitcoin:%s?amount=%s',
            $target->address,
            $this->formatBtcAmount($deposit->expected_crypto_amount)
        );
    }

    private function formatBtcAmount(float $amount): string
    {
        return number_format($amount, 8, '.', '');
    }
}
