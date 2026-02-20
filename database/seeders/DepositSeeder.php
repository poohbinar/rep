<?php

namespace Database\Seeders;

use App\Enums\Deposit\AmlRiskLevel;
use App\Enums\Deposit\CryptoAddressStatus;
use App\Enums\Deposit\CryptoTransactionStatus;
use App\Enums\Deposit\DepositStatuses;
use App\Enums\Deposit\PaymentTargetType;
use App\Models\BufferWallet;
use App\Models\CryptoAddress;
use App\Models\CryptoAmlCheck;
use App\Models\CryptoDeposit;
use App\Models\CryptoTransaction;
use App\Models\CurrencyRate;
use App\Models\PaymentTarget;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepositSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1;
        $walletId = 1;

        /*
        |--------------------------------------------------------------------------
        | Buffer wallets
        |--------------------------------------------------------------------------
        */
        $solBuffer = BufferWallet::factory()->create([
            'blockchain' => 'solana',
            'label' => 'SOL Buffer',
        ]);

        $ethBuffer = BufferWallet::factory()->create([
            'blockchain' => 'ethereum',
            'label' => 'ETH Buffer',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Currency rates
        |--------------------------------------------------------------------------
        */
        CurrencyRate::insert([
            ['currency_id' => 1, 'rate' => 100,   'updated_at' => now()],
            ['currency_id' => 2, 'rate' => 2000,  'updated_at' => now()],
            ['currency_id' => 3, 'rate' => 96000, 'updated_at' => now()],
        ]);

        $this->seedEvmLikeDeposits('solana', 'sol', $solBuffer->address, $userId, $walletId);
        $this->seedEvmLikeDeposits('ethereum', 'eth', $ethBuffer->address, $userId, $walletId);
        $this->seedBitcoinDeposits($userId, $walletId);
    }

    /*
    |--------------------------------------------------------------------------
    | SOL / ETH (buffer wallets)
    |--------------------------------------------------------------------------
    */
    private function seedEvmLikeDeposits(
        string $blockchain,
        string $method,
        string $bufferAddress,
        int $userId,
        int $walletId
    ): void {
        foreach (range(1, rand(10, 15)) as $i) {

            $status = collect([
                DepositStatuses::AWAITING_PAYMENT,
                DepositStatuses::TX_DETECTED,
                DepositStatuses::CREDITED,
                DepositStatuses::REJECTED,
            ])->random();

            $deposit = CryptoDeposit::factory()->create([
                'user_id' => $userId,
                'wallet_id' => $walletId,
                'payment_method' => $method,
                'blockchain' => $blockchain,
                'status' => $status,
                'credited_at' => $status === DepositStatuses::CREDITED
                    ? now()->subDays(rand(0, 60))
                    : null,
            ]);

            $paymentTarget = PaymentTarget::create([
                'crypto_deposit_id' => $deposit->id,
                'type' => PaymentTargetType::BUFFER,
                'blockchain' => $blockchain,
                'address' => $bufferAddress,
                'expires_at' => now()->addMinutes(30),
            ]);

            if ($status !== DepositStatuses::AWAITING_PAYMENT) {
                $this->attachTransactions(
                    blockchain: $blockchain,
                    paymentTargetId: $paymentTarget->id,
                    toAddress: $bufferAddress
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BTC (unique address per deposit)
    |--------------------------------------------------------------------------
    */
    private function seedBitcoinDeposits(int $userId, int $walletId): void
    {
        foreach (range(1, rand(10, 15)) as $i) {

            $status = collect([
                DepositStatuses::AWAITING_PAYMENT,
                DepositStatuses::TX_DETECTED,
                DepositStatuses::CREDITED,
            ])->random();

            $deposit = CryptoDeposit::factory()->create([
                'user_id' => $userId,
                'wallet_id' => $walletId,
                'payment_method' => 'btc',
                'blockchain' => 'bitcoin',
                'status' => $status,
                'credited_at' => $status === DepositStatuses::CREDITED
                    ? now()->subDays(rand(0, 60))
                    : null,
            ]);

            $paymentTarget = PaymentTarget::create([
                'crypto_deposit_id' => $deposit->id,
                'type' => PaymentTargetType::ADDRESS,
                'blockchain' => 'bitcoin',
                'address' => Str::random(34),
                'expires_at' => now()->addHours(1),
            ]);

            CryptoAddress::create([
                'blockchain' => 'bitcoin',
                'address' => $paymentTarget->address,
                'payment_target_id' => $paymentTarget->id,
                'status' => $status === DepositStatuses::AWAITING_PAYMENT
                    ? CryptoAddressStatus::NEW
                    : CryptoAddressStatus::USED,
            ]);

            if ($status !== DepositStatuses::AWAITING_PAYMENT) {
                $this->attachTransactions(
                    blockchain: 'bitcoin',
                    paymentTargetId: $paymentTarget->id,
                    toAddress: $paymentTarget->address
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transactions + AML
    |--------------------------------------------------------------------------
    */
    private function attachTransactions(
        string $blockchain,
        int $paymentTargetId,
        string $toAddress
    ): void {
        foreach (range(1, rand(1, 3)) as $i) {

            $tx = CryptoTransaction::factory()->create([
                'payment_target_id' => $paymentTargetId,
                'blockchain' => $blockchain,
                'to_address' => $toAddress,
                'status' => collect([
                    CryptoTransactionStatus::DETECTED,
                    CryptoTransactionStatus::CONFIRMED,
                    CryptoTransactionStatus::FINALIZED,
                ])->random(),
            ]);

            CryptoAmlCheck::factory()->create([
                'tx_hash' => $tx->tx_hash,
                'blockchain' => $blockchain,
                'address' => $tx->from_address,
                'risk_level' => collect([
                    AmlRiskLevel::CLEAN,
                    AmlRiskLevel::LOW,
                ])->random(),
            ]);
        }
    }
}
