<?php

namespace Tests\Feature\App\Http\Controllers\Api\V1\Finance;

use App\Enums\Deposit\DepositStatuses;
use App\Models\CryptoDeposit;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DepositControllerHistoryActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    #[Test]
    public function unauthorized_user_cannot_get_history(): void
    {
        $this->getJson(route('api.v1.finance.deposit.history'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    #[Test]
    public function authorized_user_gets_empty_history(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history'))
            ->assertOk()
            ->assertJson([
                'items' => [],
                'meta' => [
                    'total' => 0,
                ],
            ]);
    }

    #[Test]
    public function returns_only_credited_deposits(): void
    {
        $user = User::factory()->create();

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::AWAITING_PAYMENT->value,
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::REJECTED->value,
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history'))
            ->assertOk();

        $this->assertCount(1, $response->json('items'));
    }

    #[Test]
    public function history_is_filtered_by_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        CryptoDeposit::factory()->create([
            'user_id' => $otherUser->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::CREDITED->value,
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history'))
            ->assertOk();

        $this->assertCount(1, $response->json('items'));
    }

    #[Test]
    public function filters_by_date_from(): void
    {
        $user = User::factory()->create();
        $dateFrom = now()->subDay()->format('Y-m-d H:i:s');

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now()->subDays(10),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now(),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'date_from' => $dateFrom,
            ]))
            ->assertOk();

        $this->assertCount(1, $response->json('items'));
    }

    #[Test]
    public function filters_by_date_to(): void
    {
        $user = User::factory()->create();
        $dateTo = now()->subDay()->format('Y-m-d H:i:s');

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now()->subDays(5),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now(),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'date_to' => $dateTo,
            ]))
            ->assertOk();

        $this->assertCount(1, $response->json('items'));
    }

    #[Test]
    public function filters_by_datetime_precision_on_same_day(): void
    {
        $user = User::factory()->create();
        $boundary = now()->subDay()->setTime(12, 0, 0);

        $beforeBoundary = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => $boundary->copy()->subSecond(),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $afterBoundary = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => $boundary->copy()->addSecond(),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'date_from' => $boundary->format('Y-m-d H:i:s'),
            ]))
            ->assertOk();

        $ids = collect($response->json('items'))->pluck('public_id');

        $this->assertFalse($ids->contains($beforeBoundary->public_id));
        $this->assertTrue($ids->contains($afterBoundary->public_id));
    }

    #[Test]
    public function supports_sorting(): void
    {
        $user = User::factory()->create();

        $old = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now()->subDays(5),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $new = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now(),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'sort' => 'asc',
            ]))
            ->assertOk();

        $ids = collect($response->json('items'))->pluck('public_id');

        $this->assertSame($old->public_id, $ids->first());
        $this->assertSame($new->public_id, $ids->last());
    }

    #[Test]
    public function supports_pagination(): void
    {
        $user = User::factory()->create();

        CryptoDeposit::factory()->count(15)->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'per_page' => 10,
            ]))
            ->assertOk();

        $this->assertCount(10, $response->json('items'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    #[Test]
    public function response_has_expected_structure(): void
    {
        $user = User::factory()->create();

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history'))
            ->assertOk()
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'public_id',
                        'status',
                        'blockchain',
                        'expected_crypto_amount',
                        'usd_amount',
                        'credited_at',
                        'is_final',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    #[Test]
    public function default_sorting_is_desc_by_credited_at(): void
    {
        $user = User::factory()->create();

        $old = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now()->subDays(5),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $new = CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now(),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history'))
            ->assertOk();

        $ids = collect($response->json('items'))->pluck('public_id');

        $this->assertSame($new->public_id, $ids->first());
    }

    #[Test]
    public function supports_page_number(): void
    {
        $user = User::factory()->create();

        CryptoDeposit::factory()->count(15)->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'per_page' => 10,
                'page_number' => 2,
            ]))
            ->assertOk();

        $this->assertCount(5, $response->json('items'));
    }

    #[Test]
    public function date_to_is_auto_filled_when_only_date_from_provided(): void
    {
        $user = User::factory()->create();
        $dateFrom = now()->subDays(7)->format('Y-m-d H:i:s');

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'credited_at' => now()->subDays(2),
            'status' => DepositStatuses::CREDITED->value,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'date_from' => $dateFrom,
            ]))
            ->assertOk();

        $this->assertNotEmpty($response->json('items'));
    }

    #[Test]
    public function validation_fails_if_date_to_before_date_from(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history', [
                'date_from' => '2026-02-10 00:00:00',
                'date_to' => '2026-01-01 00:00:00',
            ]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }

    #[Test]
    public function deposits_without_credited_at_are_not_returned(): void
    {
        $user = User::factory()->create();

        CryptoDeposit::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => 1,
            'status' => DepositStatuses::CREDITED->value,
            'credited_at' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.finance.deposit.history'))
            ->assertOk()
            ->assertJson([
                'items' => [],
            ]);
    }
}
