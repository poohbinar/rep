<?php

namespace Tests\Feature\App\Http\Controllers\Api\V1\Finance;

use App\Contracts\BtcClientInterface;
use App\Contracts\EthClientInterface;
use App\Contracts\SolClientInterface;
use App\Enums\Deposit\DepositStatuses;
use App\Enums\Deposit\PaymentMethodEnum;
use App\Models\BufferWallet;
use App\Models\CryptoDeposit;
use App\Models\PaymentTarget;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DepositControllerCreateCryptoActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(BtcClientInterface::class, fn () => new class implements BtcClientInterface
        {
            public function createAddress(): string
            {
                return 'bc1qtestaddress0000000000000000000000000001';
            }
        });

        $this->app->singleton(EthClientInterface::class, fn () => new class implements EthClientInterface
        {
            public function createAddress(): string
            {
                return '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
            }
        });

        $this->app->singleton(SolClientInterface::class, fn () => new class implements SolClientInterface
        {
            public function createAddress(): string
            {
                return 'SoLTESTADDRESS1111111111111111111111111111111';
            }

            public function getSignaturesForAddress(string $address, int $limit = 20): array
            {
                return [];
            }

            public function getTransaction(string $signature): array
            {
                return [];
            }
        });

        $this->seed(CurrencySeeder::class);
    }

    #[Test]
    public function unauthorized_user_cannot_create_crypto_deposit(): void
    {
        $this->postJson(route('api.v1.finance.deposit.crypto.create'), [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 10],
        ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    #[Test]
    public function it_creates_sol_deposit_and_returns_payment_details(): void
    {
        $user = $this->authenticateUser();

        BufferWallet::factory()->create([
            'blockchain' => 'solana',
            'address' => 'SoL4Nc4TcH4ddr35511111111111111111111111111',
            'is_active' => true,
        ]);

        $response = $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 15.5],
        ])->assertOk();

        $response->assertJsonStructure([
            'public_id',
            'status',
            'blockchain',
            'expected_crypto_amount',
            'usd_amount',
            'credited_at',
            'is_final',
            'payment' => [
                'address',
                'memo',
                'expires_at',
                'type',
            ],
            'qr',
        ]);

        $response->assertJsonPath('status', 'awaiting_payment');
        $response->assertJsonPath('is_final', false);
        $response->assertJsonPath('blockchain', 'solana');
        $response->assertJsonPath('payment.type', 'buffer');
        $response->assertJsonPath('payment.address', 'SoL4Nc4TcH4ddr35511111111111111111111111111');
        $publicId = $response->json('public_id');

        $deposit = CryptoDeposit::query()
            ->where('public_id', $publicId)
            ->firstOrFail();

        $this->assertSame($user->id, $deposit->user_id);
        $this->assertSame($user->wallet->id, $deposit->wallet_id);
        $this->assertSame(PaymentMethodEnum::SOL->value, $deposit->payment_method);
        $this->assertSame(DepositStatuses::AWAITING_PAYMENT, $deposit->status);

        $this->assertDatabaseHas('payment_targets', [
            'crypto_deposit_id' => $deposit->id,
            'blockchain' => 'solana',
            'type' => 'buffer',
            'address' => 'SoL4Nc4TcH4ddr35511111111111111111111111111',
        ]);
    }

    #[Test]
    public function it_creates_btc_deposit_with_newly_generated_address(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::BTC->value,
            'fields' => ['amount' => 0.5],
        ])->assertOk();

        $publicId = $response->json('public_id');
        $deposit = CryptoDeposit::query()->where('public_id', $publicId)->firstOrFail();
        $target = PaymentTarget::query()->where('crypto_deposit_id', $deposit->id)->firstOrFail();

        $this->assertSame('address', $target->type->value);
        $this->assertSame('bitcoin', $target->blockchain);
        $this->assertStringStartsWith('bc1q', $target->address);
        $this->assertSame('bc1qtestaddress0000000000000000000000000001', $target->address);

        $this->assertDatabaseHas('crypto_addresses', [
            'blockchain' => 'bitcoin',
            'address' => $target->address,
            'payment_target_id' => $target->id,
        ]);
    }

    #[Test]
    public function it_creates_eth_deposit_and_returns_memo(): void
    {
        $user = $this->authenticateUser();

        BufferWallet::factory()->create([
            'blockchain' => 'ethereum',
            'address' => '0x1111111111111111111111111111111111111111',
            'is_active' => true,
        ]);

        $response = $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::ETH->value,
            'fields' => ['amount' => 2],
        ])->assertOk();

        $response->assertJsonPath('payment.type', 'buffer');
        $response->assertJsonPath('payment.address', '0x1111111111111111111111111111111111111111');
        $this->assertStringStartsWith('deposit:', (string) $response->json('payment.memo'));
        $this->assertStringStartsWith('ethereum:0x1111111111111111111111111111111111111111?value=', (string) $response->json('qr'));
    }

    #[Test]
    public function it_accepts_optional_promo_code(): void
    {
        $user = $this->authenticateUser();

        BufferWallet::factory()->create([
            'blockchain' => 'solana',
            'is_active' => true,
        ]);

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 2],
            'promo_code' => 'WELCOME10',
        ])->assertOk();
    }

    #[Test]
    public function it_returns_404_when_payment_method_not_found(): void
    {
        $user = $this->authenticateUser();

        $this->postCreateDeposit($user, [
            'payment_method' => 'invalid',
            'fields' => ['amount' => 2],
        ])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Payment method "invalid" not found.',
            ]);
    }

    #[Test]
    public function it_validates_required_payment_method(): void
    {
        $user = $this->authenticateUser();

        $this->postCreateDeposit($user, [
            'fields' => ['amount' => 2],
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'success' => false,
                'message' => 'The payment method field is required.',
                'errors' => [
                    'payment_method' => ['The payment method field is required.'],
                ],
            ])
            ->assertJsonValidationErrors(['payment_method']);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $user = $this->authenticateUser();

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['fields']);
    }

    #[Test]
    public function it_validates_fields_must_be_array(): void
    {
        $user = $this->authenticateUser();

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => 'not-an-array',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'success' => false,
                'message' => 'The fields must be an array.',
                'errors' => [
                    'fields' => ['The fields must be an array.'],
                ],
            ])
            ->assertJsonValidationErrors(['fields']);
    }

    #[Test]
    public function it_validates_amount_is_required_in_fields(): void
    {
        $user = $this->authenticateUser();

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => null],
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Amount is required',
            ]);
    }

    #[Test]
    public function it_rejects_non_numeric_amount_value(): void
    {
        $user = $this->authenticateUser();

        BufferWallet::factory()->create([
            'blockchain' => 'solana',
            'is_active' => true,
        ]);

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 'not-a-number'],
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Amount is less than minimum (1)',
            ]);
    }

    #[Test]
    public function it_rejects_amount_less_than_method_min_limit(): void
    {
        $user = $this->authenticateUser();

        BufferWallet::factory()->create([
            'blockchain' => 'solana',
            'is_active' => true,
        ]);

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 0.5],
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Amount is less than minimum (1)',
            ]);
    }

    #[Test]
    public function it_rejects_amount_greater_than_method_max_limit(): void
    {
        $user = $this->authenticateUser();

        BufferWallet::factory()->create([
            'blockchain' => 'solana',
            'is_active' => true,
        ]);

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 1000.1],
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Amount exceeds maximum (1000)',
            ]);
    }

    #[Test]
    public function it_auto_creates_sol_buffer_wallet_when_missing(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 10],
        ])->assertOk();

        $targetAddress = $response->json('payment.address');

        $this->assertNotEmpty($targetAddress);
        $this->assertSame('SoLTESTADDRESS1111111111111111111111111111111', $targetAddress);

        $this->assertDatabaseHas('buffer_wallets', [
            'blockchain' => 'solana',
            'address' => $targetAddress,
            'is_active' => 1,
        ]);
    }

    #[Test]
    public function it_creates_btc_deposit_even_without_precreated_addresses(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::BTC->value,
            'fields' => ['amount' => 1],
        ])->assertOk();

        $targetAddress = $response->json('payment.address');

        $this->assertSame('bc1qtestaddress0000000000000000000000000001', (string) $targetAddress);
        $this->assertDatabaseHas('crypto_addresses', [
            'blockchain' => 'bitcoin',
            'address' => $targetAddress,
        ]);
    }

    #[Test]
    public function it_returns_500_and_rolls_back_when_btc_rpc_address_generation_fails(): void
    {
        // TODO проверить, точно ли этот тест должен 500 возвращать
        $user = $this->authenticateUser();

        $this->app->singleton(BtcClientInterface::class, fn () => new class implements BtcClientInterface
        {
            public function createAddress(): string
            {
                throw new RuntimeException('BTC RPC unavailable');
            }
        });

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::BTC->value,
            'fields' => ['amount' => 1],
        ])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'success' => false,
                'message' => 'BTC RPC unavailable',
            ]);

        $this->assertDatabaseCount('crypto_deposits', 0);
        $this->assertDatabaseCount('payment_targets', 0);
        $this->assertDatabaseCount('crypto_addresses', 0);
    }

    #[Test]
    public function it_returns_500_and_rolls_back_when_sol_rpc_wallet_generation_fails(): void
    {
        $user = $this->authenticateUser();

        $this->app->singleton(SolClientInterface::class, fn () => new class implements SolClientInterface
        {
            public function createAddress(): string
            {
                throw new RuntimeException('SOL RPC unavailable');
            }

            public function getSignaturesForAddress(string $address, int $limit = 20): array
            {
                return [];
            }

            public function getTransaction(string $signature): array
            {
                return [];
            }
        });

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::SOL->value,
            'fields' => ['amount' => 1],
        ])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'success' => false,
                'message' => 'SOL RPC unavailable',
            ]);

        $this->assertDatabaseCount('crypto_deposits', 0);
        $this->assertDatabaseCount('payment_targets', 0);
        $this->assertDatabaseMissing('buffer_wallets', [
            'blockchain' => 'solana',
            'is_active' => 1,
        ]);
    }

    #[Test]
    public function it_returns_500_and_rolls_back_when_eth_rpc_wallet_generation_fails(): void
    {
        $user = $this->authenticateUser();

        $this->app->singleton(EthClientInterface::class, fn () => new class implements EthClientInterface
        {
            public function createAddress(): string
            {
                throw new RuntimeException('ETH RPC unavailable');
            }
        });

        $this->postCreateDeposit($user, [
            'payment_method' => PaymentMethodEnum::ETH->value,
            'fields' => ['amount' => 1],
        ])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'success' => false,
                'message' => 'ETH RPC unavailable',
            ]);

        $this->assertDatabaseCount('crypto_deposits', 0);
        $this->assertDatabaseCount('payment_targets', 0);
        $this->assertDatabaseMissing('buffer_wallets', [
            'blockchain' => 'ethereum',
            'is_active' => 1,
        ]);
    }

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $user->profile->update(['country' => 'NL']);

        return $user;
    }

    private function postCreateDeposit(User $user, array $payload): TestResponse
    {
        return $this
            ->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.finance.deposit.crypto.create'), $payload);
    }
}
