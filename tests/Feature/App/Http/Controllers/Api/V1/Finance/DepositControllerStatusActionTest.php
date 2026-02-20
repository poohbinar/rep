<?php

namespace Tests\Feature\App\Http\Controllers\Api\V1\Finance;

use App\Enums\Deposit\CryptoTransactionStatus;
use App\Enums\Deposit\DepositStatuses;
use App\Models\CryptoDeposit;
use App\Models\CryptoTransaction;
use App\Models\PaymentTarget;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DepositControllerStatusActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    #[Test]
    public function unauthorized_user_cannot_get_deposit_status(): void
    {
        $this->getJson(route('api.v1.finance.deposit.status', [
            'publicId' => '6e8f4e02-c91c-465f-b22d-7f102fca381b',
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    #[Test]
    public function user_gets_current_deposit_status_with_payment_data_and_confirmations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $deposit = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'status' => DepositStatuses::AWAITING_PAYMENT,
            'blockchain' => 'solana',
            'payment_method' => 'sol',
        ]);

        $target = PaymentTarget::factory()->create([
            'crypto_deposit_id' => $deposit->id,
            'type' => 'buffer',
            'blockchain' => 'solana',
            'address' => 'SoLSt4tus4ddr3551111111111111111111111111111',
        ]);

        CryptoTransaction::factory()->create([
            'payment_target_id' => $target->id,
            'confirmations' => 2,
            'status' => CryptoTransactionStatus::DETECTED,
        ]);

        CryptoTransaction::factory()->create([
            'payment_target_id' => $target->id,
            'confirmations' => 7,
            'status' => CryptoTransactionStatus::CONFIRMED,
        ]);

        $this->getJson(route('api.v1.finance.deposit.status', [
            'publicId' => $deposit->public_id,
        ]))
            ->assertOk()
            ->assertJsonPath('public_id', $deposit->public_id)
            ->assertJsonPath('status', 'awaiting_payment')
            ->assertJsonPath('is_final', false)
            ->assertJsonPath('blockchain', 'solana')
            ->assertJsonPath('payment.address', 'SoLSt4tus4ddr3551111111111111111111111111111')
            ->assertJsonPath('payment.type', 'buffer')
            ->assertJsonPath('confirmations', 7);
    }

    #[Test]
    public function status_endpoint_reflects_status_change_in_database(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $deposit = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'status' => DepositStatuses::AWAITING_PAYMENT,
            'blockchain' => 'bitcoin',
            'payment_method' => 'btc',
        ]);

        PaymentTarget::factory()->create([
            'crypto_deposit_id' => $deposit->id,
            'type' => 'address',
            'blockchain' => 'bitcoin',
            'address' => 'bc1qstatuschange1111111111111111111111111111',
        ]);

        $this->getJson(route('api.v1.finance.deposit.status', [
            'publicId' => $deposit->public_id,
        ]))
            ->assertOk()
            ->assertJsonPath('status', 'awaiting_payment')
            ->assertJsonPath('is_final', false)
            ->assertJsonPath('payment.type', 'address');

        $deposit->update([
            'status' => DepositStatuses::CREDITED,
            'usd_amount' => 150.75,
            'credited_at' => now(),
        ]);

        $this->getJson(route('api.v1.finance.deposit.status', [
            'publicId' => $deposit->public_id,
        ]))
            ->assertOk()
            ->assertJsonPath('status', 'credited')
            ->assertJsonPath('is_final', true)
            ->assertJsonPath('usd_amount', '150.75000000')
            ->assertJsonMissingPath('payment')
            ->assertJsonMissingPath('qr');
    }

    #[Test]
    public function user_cannot_get_foreign_or_missing_deposit_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $foreignDeposit = CryptoDeposit::factory()->create([
            'user_id' => $otherUser->id,
            'wallet_id' => $otherUser->wallet->id,
        ]);

        $this->getJson(route('api.v1.finance.deposit.status', [
            'publicId' => $foreignDeposit->public_id,
        ]))
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => "Deposit {$foreignDeposit->public_id} was not found.",
                'code' => Response::HTTP_NOT_FOUND,
            ]);
    }

    #[Test]
    public function status_does_not_fail_for_awaiting_payment_deposit_without_payment_target(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $deposit = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'status' => DepositStatuses::AWAITING_PAYMENT,
            'blockchain' => 'solana',
            'payment_method' => 'sol',
        ]);

        $this->getJson(route('api.v1.finance.deposit.status', [
            'publicId' => $deposit->public_id,
        ]))
            ->assertOk()
            ->assertJsonPath('public_id', $deposit->public_id)
            ->assertJsonPath('status', 'awaiting_payment')
            ->assertJsonPath('is_final', false)
            ->assertJsonMissingPath('payment')
            ->assertJsonMissingPath('qr');
    }
}
