<?php

namespace App\Services\Deposit;

use App\Contracts\PaymentMethodConfigInterface;
use App\Data\DepositHistoryFilterData;
use App\Enums\Deposit\DepositStatuses;
use App\Enums\Deposit\PaymentMethodEnum;
use App\Exceptions\ApiException;
use App\Exceptions\Deposit\DepositNotFoundException;
use App\Models\CryptoDeposit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DepositService
{
    public function __construct(
        private readonly PaymentMethodManager $paymentMethodManager
    ) {}

    public function getAvailableMethods(User $user): array
    {
        return collect(PaymentMethodEnum::cases())
            ->map(fn ($e) => $this->paymentMethodManager->getConfig($e->value))
            ->filter(fn ($cfg) => $cfg->isEnabled())
            ->filter(fn ($cfg) => app()->environment('production')
                ? ! $cfg->isDevOnly()
                : true
            )
            ->filter(fn ($cfg) => in_array('*', $cfg->allowedCountries(), true)
                || in_array($user->profile->country, $cfg->allowedCountries(), true)
            )
            ->sortByDesc(fn ($cfg) => $cfg->priority())
            ->map(fn ($cfg) => [
                'code' => $cfg::getName(),
                'title' => $cfg->title(),
                'description' => $cfg->description(),
                'dev_only' => $cfg->isDevOnly(),
                'enabled' => $cfg->isEnabled(),
                'allowed_countries' => $cfg->allowedCountries(),
                'priority' => $cfg->priority(),
                'min_amount' => $cfg->minAmount(),
                'max_amount' => $cfg->maxAmount(),
                'meta' => $cfg->meta(),
            ])
            ->values()
            ->toArray();
    }

    public function getUserPopularMethods(User $user): Collection
    {
        $countryCode = $user->profile->country ?: $user->profile->country_ip;

        $popularBlockchains = CryptoDeposit::query()
            ->select('blockchain', DB::raw('COUNT(*) as total'))
            ->where('user_id', $user->id)
            ->where('status', DepositStatuses::CREDITED->value)
            ->groupBy('blockchain')
            ->orderByDesc('total')
            ->pluck('blockchain')
            ->toArray();

        if (empty($popularBlockchains)) {
            return collect();
        }

        $popularMethods = collect($popularBlockchains)
            ->map(fn (string $blockchain) => match ($blockchain) {
                'solana' => PaymentMethodEnum::SOL,
                'ethereum' => PaymentMethodEnum::ETH,
                'bitcoin' => PaymentMethodEnum::BTC,
                default => null,
            })
            ->filter();

        return $popularMethods
            ->map(fn ($enum) => $this->paymentMethodManager->getConfig($enum->value))
            ->filter(fn ($cfg) => $cfg->isEnabled())
            ->filter(fn ($cfg) => app()->environment('production')
                ? ! $cfg->isDevOnly()
                : true
            )
            ->filter(fn ($cfg) => in_array('*', $cfg->allowedCountries(), true)
                || in_array($countryCode, $cfg->allowedCountries(), true)
            )
            ->sortBy(fn ($cfg) => array_search(
                $cfg->meta()['blockchain'],
                $popularBlockchains,
                true
            )
            )
            ->map(fn ($cfg) => [
                'code' => $cfg::getName(),
                'title' => $cfg->title(),
                'description' => $cfg->description(),
                'dev_only' => $cfg->isDevOnly(),
                'enabled' => $cfg->isEnabled(),
                'allowed_countries' => $cfg->allowedCountries(),
                'priority' => $cfg->priority(),
                'min_amount' => $cfg->minAmount(),
                'max_amount' => $cfg->maxAmount(),
                'meta' => $cfg->meta(),
            ])
            ->values();
    }

    public function isCountryAllowed(PaymentMethodConfigInterface $paymentMethodConfig, string $countryCode): bool
    {
        if (! is_array($paymentMethodConfig->allowedCountries())) {
            return false;
        }

        // Если доступен для всех стран
        if (in_array('*', $paymentMethodConfig->allowedCountries(), true)) {
            return true;
        }

        return $countryCode && in_array($countryCode, $paymentMethodConfig->allowedCountries(), true);
    }

    public function getDepositInfo(User $user, string $publicId): CryptoDeposit
    {
        $deposit = CryptoDeposit::with(['paymentTarget', 'transactions'])
            ->where('public_id', $publicId)
            ->where('user_id', $user->id)
            ->first();

        if ($deposit === null) {
            throw new DepositNotFoundException(
                __('Deposit :publicId was not found.', ['publicId' => $publicId]));
        }

        return $deposit;
    }

    public function getDepositHistory(User $user, DepositHistoryFilterData $filters): LengthAwarePaginator
    {
        $query = CryptoDeposit::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                DepositStatuses::CREDITED->value,
            ]);

        if ($filters->dateFrom) {
            $query->where('credited_at', '>=', $filters->dateFrom);
        }

        if ($filters->dateTo) {
            $query->where('credited_at', '<=', $filters->dateTo);
        }

        $query->orderBy(
            $filters->sortField->value,
            $filters->sort->value
        );

        return $query->paginate(
            perPage: $filters->perPage,
            page: $filters->pageNumber
        );
    }

    public function getMethodForm(User $user, string $method): array
    {
        $country = $user->profile->country ?? $user->profile->country_ip;

        if (! PaymentMethodEnum::tryFrom($method)) {
            throw new ApiException('Payment method not found.', Response::HTTP_NOT_FOUND);
        }

        $config = $this->paymentMethodManager->getConfig($method);

        if (! $config->isEnabled()) {
            throw new ApiException('Payment method is disabled.', Response::HTTP_BAD_REQUEST);
        }

        if (app()->environment('production') && $config->isDevOnly()) {
            throw new ApiException('Payment method is not available.', Response::HTTP_FORBIDDEN);
        }

        if (
            ! in_array('*', $config->allowedCountries(), true)
            && ! in_array($country, $config->allowedCountries(), true)
        ) {
            throw new ApiException('Payment method is not available in your country.', Response::HTTP_FORBIDDEN);
        }

        return [
            'method' => $config::getName(),

            'title' => $config->title(),
            'description' => $config->description(),

            'min_amount' => $config->minAmount(),
            'max_amount' => $config->maxAmount(),

            'currency' => $config->meta()['currency'],
            'blockchain' => $config->meta()['blockchain'],
            'decimals' => $config->meta()['decimals'],

            'fields' => [
                [
                    'name' => 'amount',
                    'type' => 'number',
                    'required' => true,
                    'decimals' => $config->meta()['decimals'],
                ],
                [
                    'name' => 'promo_code',
                    'type' => 'string',
                    'required' => false,
                ],
            ],
        ];
    }

    public function createDeposit(
        User $user,
        string $method,
        array $fields,
        ?string $promoCode = null
    ): CryptoDeposit {

        // 1. validate limits
        // 2. apply promo (optional)
        // 3. create deposit
        // 4. generate crypto address
        // 5. attach address to deposit
        // 6. return deposit + address

        $config = $this->paymentMethodManager->getConfig($method);
        $provider = $this->paymentMethodManager->getProvider($method);

        $provider->validateAvailability();
        $provider->validateFields($fields);

        $amount = $provider->extractAmount($fields);
        $provider->validateAmount($amount);

        return DB::transaction(function () use (
            $user,
            $amount,
            $method,
            $promoCode,
            $provider,
            $config
        ) {
            $deposit = CryptoDeposit::create([
                'public_id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'payment_method' => $method,
                'wallet_id' => $user->wallet->id,
                'blockchain' => $config->meta()['blockchain'],
                'expected_crypto_amount' => $amount,
                'status' => DepositStatuses::AWAITING_PAYMENT,
            ]);

            $paymentTarget = $provider->getPaymentTarget($deposit);
            $deposit->setRelation('paymentTarget', $paymentTarget);

            if ($promoCode) {
                // TODO: Apply promo
                // PromoService::applyToDeposit($deposit, $promoCode);
            }

            $deposit->setAttribute(
                'payment_instruction',
                $provider->buildPaymentInstruction($deposit)
            );

            return $deposit;
        });
    }
}
