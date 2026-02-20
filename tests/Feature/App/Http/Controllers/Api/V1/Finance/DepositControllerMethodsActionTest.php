<?php

namespace Tests\Feature\App\Http\Controllers\Api\V1\Finance;

use App\Enums\Deposit\DepositStatuses;
use App\Models\CryptoDeposit;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DepositControllerMethodsActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    #[Test]
    public function test_unauthorized_user_cannot_access_endpoints(): void
    {
        $unauthenticatedResponse = [
            'success' => false,
            'message' => 'Unauthorized.',
        ];

        $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($unauthenticatedResponse);

        $this->getJson(route('api.v1.finance.deposit.history'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($unauthenticatedResponse);

        $this->getJson(route('api.v1.finance.deposit.status',
            ['publicId' => Uuid::create()]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($unauthenticatedResponse);

        $this->getJson(route('api.v1.finance.deposit.method.form',
            ['method' => 'sol']
        ))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($unauthenticatedResponse);

        $this->postJson(
            route('api.v1.finance.deposit.crypto.create'),
            [
                'paymentMethod' => 'sol',
                'amount' => 1,
            ]
        )
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($unauthenticatedResponse);
    }

    #[Test]
    public function authorized_user_can_get_available_deposit_methods(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'code',
                        'title',
                        'description',
                        'priority',
                        'dev_only',
                        'enabled',
                        'allowed_countries',
                        'config' => [
                            'min_amount',
                            'max_amount',
                        ],
                    ],
                ],
                'popular',
            ]);
    }

    #[Test]
    public function popular_methods_are_empty_for_new_user(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'));

        $response
            ->assertOk()
            ->assertJson([
                'popular' => [],
            ]);
    }

    #[Test]
    public function methods_are_filtered_by_user_country(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'XX',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'));

        $methods = collect($response->json('items'));

        $this->assertTrue(
            $methods->every(fn ($method) => in_array('*', $method['allowed_countries'], true)
                || in_array('XX', $method['allowed_countries'], true)
            )
        );
    }

    #[Test]
    public function dev_only_methods_are_hidden_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'));

        $methods = collect($response->json('items'));

        $this->assertFalse(
            $methods->contains(fn ($method) => $method['dev_only'] === true)
        );
    }

    #[Test]
    public function popular_methods_are_sorted_by_user_usage(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        CryptoDeposit::factory()->count(3)->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'blockchain' => 'solana',
            'status' => DepositStatuses::CREDITED->value,
        ]);

        CryptoDeposit::factory()->count(1)->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'blockchain' => 'bitcoin',
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertOk();

        $popular = collect($response->json('popular'));

        $this->assertCount(2, $popular);

        $this->assertSame(
            ['sol', 'btc'],
            $popular->pluck('code')->toArray()
        );
    }

    #[Test]
    public function disabled_methods_are_not_returned(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertOk();

        $this->assertTrue(
            collect($response->json('items'))
                ->every(fn ($m) => $m['enabled'] === true)
        );
    }

    #[Test]
    public function deposit_methods_are_sorted_by_priority_desc(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertOk();

        $priorities = collect($response->json('items'))
            ->pluck('priority')
            ->toArray();

        $sorted = $priorities;
        rsort($sorted);

        $this->assertSame($sorted, $priorities);
    }

    #[Test]
    public function popular_methods_do_not_include_unavailable_ones(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'blockchain' => 'ethereum',
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertOk();

        $popular = collect($response->json('popular'));

        $this->assertTrue(
            $popular->every(fn ($m) => $m['enabled'] ?? true
            )
        );
    }

    #[Test]
    public function global_methods_are_available_for_any_country(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'ZZ',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertOk();

        $this->assertNotEmpty(
            collect($response->json('items'))
                ->filter(fn ($m) => in_array('*', $m['allowed_countries'], true))
        );
    }

    #[Test]
    public function popular_methods_have_expected_structure(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'blockchain' => 'solana',
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $this->actingAs($user, 'sanctum');

        $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertOk()
            ->assertJsonStructure([
                'popular' => [
                    '*' => [
                        'code',
                        'title',
                        'description',
                        'priority',
                        'dev_only',
                        'enabled',
                        'allowed_countries',
                        'config' => [
                            'min_amount',
                            'max_amount',
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function unknown_blockchain_is_ignored_in_popular_methods(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'blockchain' => 'unknown_chain',
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $this->actingAs($user, 'sanctum');

        $this->getJson(route('api.v1.finance.deposit.methods'))
            ->assertOk()
            ->assertJson([
                'popular' => [],
            ]);
    }
}
