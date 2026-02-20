<?php

namespace App\Services\Deposit;

use App\Contracts\PaymentMethodConfigInterface;
use App\Contracts\PaymentMethodProviderInterface;
use App\Exceptions\Deposit\PaymentMethodNotFoundException;
use Illuminate\Support\Collection;

class PaymentMethodManager
{
    protected Collection $configs;

    protected Collection $providers;

    public function __construct(
        iterable $configs,
        iterable $providers,
    ) {
        $this->configs = collect();
        $this->providers = collect();

        foreach ($configs as $config) {
            $this->configs->put($config::getName(), $config);
        }

        foreach ($providers as $provider) {
            $this->providers->put($provider::getName(), $provider);
        }
    }

    public function getConfig(string $name): PaymentMethodConfigInterface
    {
        if (! $this->configs->has($name)) {
            throw new PaymentMethodNotFoundException($name);
        }

        return $this->configs->get($name);
    }

    public function getProvider(string $name): PaymentMethodProviderInterface
    {
        if (! $this->providers->has($name)) {
            throw new PaymentMethodNotFoundException($name);
        }

        return $this->providers->get($name);
    }
}
