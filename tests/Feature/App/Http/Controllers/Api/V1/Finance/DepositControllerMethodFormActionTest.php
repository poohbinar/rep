<?php

namespace Tests\Feature\App\Http\Controllers\Api\V1\Finance;

use App\Enums\Deposit\PaymentMethodEnum;
use App\Models\User;
use App\Services\Deposit\PaymentMethodManager;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Fakes\FakeCountryRestrictedPaymentMethodConfig;
use Tests\Fakes\FakeDevOnlyPaymentMethodConfig;
use Tests\Fakes\FakeDisabledPaymentMethodConfig;
use Tests\Fakes\FakePaymentMethodManager;
use Tests\TestCase;

class DepositControllerMethodFormActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    #[Test]
    public function authorized_user_can_get_deposit_method_form(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route('api.v1.finance.deposit.method.form', PaymentMethodEnum::SOL->value)
            )
            ->assertOk()
            ->assertJsonStructure([
                'method',
                'blockchain',
                'currency',
                'limits' => [
                    'min',
                    'max',
                ],
                'fields' => [
                    [
                        'name',
                        'type',
                        'required',
                    ],
                ],
                'promo_available',
            ])
            ->assertJson([
                'method' => 'sol',
            ]);
    }

    #[Test]
    public function returns_404_if_payment_method_does_not_exist(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route('api.v1.finance.deposit.method.form', 'invalid_method')
            )
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Payment method not found.',
            ]);
    }

    #[Test]
    public function returns_error_if_method_is_disabled(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->app->bind(
            PaymentMethodManager::class,
            fn () => new FakePaymentMethodManager(
                configs: [
                    new FakeDisabledPaymentMethodConfig,
                ]
            )
        );

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route('api.v1.finance.deposit.method.form', PaymentMethodEnum::SOL->value)
            )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'success' => false,
                'message' => 'Payment method is disabled.',
            ]);
    }

    #[Test]
    public function returns_error_if_method_is_dev_only_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'NL',
        ]);

        $this->app
            ->bind(
                PaymentMethodManager::class,
                fn () => new FakePaymentMethodManager(
                    configs: [
                        new FakeDevOnlyPaymentMethodConfig,
                    ]
                )
            );

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route('api.v1.finance.deposit.method.form', PaymentMethodEnum::SOL->value)
            )
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Payment method is not available.',
            ]);
    }

    #[Test]
    public function returns_error_if_method_is_not_available_in_user_country(): void
    {
        $user = User::factory()->create();

        $user->profile->update([
            'country' => 'US',
        ]);

        $this->app->bind(
            PaymentMethodManager::class,
            fn () => new FakePaymentMethodManager(
                configs: [
                    new FakeCountryRestrictedPaymentMethodConfig,
                ]
            )
        );

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route('api.v1.finance.deposit.method.form', PaymentMethodEnum::SOL->value)
            )
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Payment method is not available in your country.',
            ]);
    }

    #[Test]
    public function method_form_returns_correct_data(): void
    {
        $user = User::factory()->create();
        $user->profile->update(['country' => 'NL']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(
                route(
                    'api.v1.finance.deposit.method.form',
                    PaymentMethodEnum::SOL->value
                )
            )
            ->assertOk();

        $response->assertJson([
            'method' => 'sol',
            'currency' => 'SOL',
            'blockchain' => 'solana',
            'promo_available' => true,
        ]);

        $response->assertJsonPath('limits.min', 1);
        $response->assertJsonPath('limits.max', 1000);
        $response->assertJsonPath('fields.0.name', 'amount');
        $response->assertJsonPath('fields.0.type', 'number');
        $response->assertJsonPath('fields.0.required', true);
    }

    #[Test]
    public function method_form_contains_decimals(): void
    {
        $user = User::factory()->create();
        $user->profile->update(['country' => 'NL']);

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route(
                    'api.v1.finance.deposit.method.form',
                    PaymentMethodEnum::SOL->value
                )
            )
            ->assertOk()
            ->assertJsonPath('fields.0.decimals', 9);
    }

    #[Test]
    public function method_form_has_expected_fields_structure(): void
    {
        $user = User::factory()->create();
        $user->profile->update(['country' => 'NL']);

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route(
                    'api.v1.finance.deposit.method.form',
                    PaymentMethodEnum::SOL->value
                )
            )
            ->assertOk()
            ->assertJsonStructure([
                'fields' => [
                    [
                        'name',
                        'type',
                        'required',
                        'decimals',
                    ],
                ],
            ]);
    }

    #[Test]
    public function promo_available_is_true_by_default(): void
    {
        $user = User::factory()->create();
        $user->profile->update(['country' => 'NL']);

        $this->actingAs($user, 'sanctum')
            ->getJson(
                route(
                    'api.v1.finance.deposit.method.form',
                    PaymentMethodEnum::SOL->value
                )
            )
            ->assertOk()
            ->assertJson([
                'promo_available' => true,
            ]);
    }
}
