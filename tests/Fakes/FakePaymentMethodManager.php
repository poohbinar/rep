<?php

namespace Tests\Fakes;

use App\Contracts\PaymentMethodProviderInterface;
use App\Services\Deposit\PaymentMethodManager;

final class FakePaymentMethodManager extends PaymentMethodManager
{
    public function __construct(array $configs = [], array $providers = [])
    {
        parent::__construct(
            configs: $configs,
            providers: $providers
        );
    }

    public function getProvider(string $name): PaymentMethodProviderInterface
    {
        throw new \RuntimeException('Provider not needed for this test');
    }
}
