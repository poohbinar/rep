<?php

namespace App\Services\Deposit\Providers;

use App\Contracts\PaymentMethodConfigInterface;
use App\Contracts\PaymentMethodProviderInterface;
use App\Enums\Deposit\PaymentMethodEnum;
use App\Exceptions\Deposit\InvalidDepositAmountException;
use App\Models\CryptoDeposit;
use App\Models\PaymentTarget;
use App\Services\BufferWalletService;

class EthPaymentMethodProvider implements PaymentMethodProviderInterface
{
    public function __construct(
        private readonly PaymentMethodConfigInterface $config,
        private readonly BufferWalletService $bufferWalletService,
    ) {}

    public static function getName(): string
    {
        return PaymentMethodEnum::ETH->value;
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
     * Создаёт payment_target для ETH (buffer wallet)
     */
    public function getPaymentTarget(CryptoDeposit $deposit): PaymentTarget
    {
        $wallet = $this->bufferWalletService->getActiveWallet('ethereum');

        return PaymentTarget::create([
            'crypto_deposit_id' => $deposit->id,
            'type' => 'buffer',
            'blockchain' => 'ethereum',
            'address' => $wallet->address,
            'memo' => 'deposit:' . $deposit->public_id,
            'expires_at' => now()->addMinutes(20),
        ]);
    }

    public function estimateNetworkFee(): float
    {
        // ETH network fee сильно плавает, лучше возвращать estimate или 0
        return 0.0;
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

        $wei = $this->ethToWei($deposit->expected_crypto_amount);

        // EIP-681
        // ethereum:<address>?value=<wei>
        return sprintf(
            'ethereum:%s?value=%s',
            $target->address,
            $wei
        );
    }

    private function ethToWei(float $eth): string
    {
        return bcmul((string) $eth, '1000000000000000000', 0);
    }
}
