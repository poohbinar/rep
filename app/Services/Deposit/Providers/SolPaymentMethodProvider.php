<?php

namespace App\Services\Deposit\Providers;

use App\Contracts\PaymentMethodConfigInterface;
use App\Contracts\PaymentMethodProviderInterface;
use App\Enums\Deposit\PaymentMethodEnum;
use App\Exceptions\Deposit\InvalidDepositAmountException;
use App\Models\CryptoDeposit;
use App\Models\PaymentTarget;
use App\Services\BufferWalletService;

class SolPaymentMethodProvider implements PaymentMethodProviderInterface
{
    public function __construct(
        private readonly PaymentMethodConfigInterface $config,
        private readonly BufferWalletService $bufferWalletService,
    ) {}

    public static function getName(): string
    {
        return PaymentMethodEnum::SOL->value;
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

    /**
     * Создаёт payment_target для SOL (уникальный адрес)
     */
    public function getPaymentTarget(CryptoDeposit $deposit): PaymentTarget
    {
        $wallet = $this->bufferWalletService->getActiveWallet('solana');

        return PaymentTarget::create([
            'crypto_deposit_id' => $deposit->id,
            'type' => 'buffer',
            'blockchain' => 'solana',
            'address' => $wallet->address,
            'memo' => null,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    public function estimateNetworkFee(): float
    {
        // Solana network fee ≈ 0
        return 0.000005;
    }

    public function buildPaymentInstruction(CryptoDeposit $deposit): ?string
    {
        $target = $deposit->paymentTarget;

        if (! $target) {
            return null;
        }

        return sprintf(
            'solana:%s?amount=%s',
            $target->address,
            $deposit->expected_crypto_amount
        );
    }
}
