<?php

namespace Tests\Unit\App\Services;

use App\Exceptions\Deposit\PaymentMethodNotFoundException;
use App\Services\Deposit\Configs\BtcPaymentMethodConfig;
use App\Services\Deposit\PaymentMethodManager;
use PHPUnit\Framework\Attributes\Test;
use Tests\Stubs\TestBtcProvider;
use Tests\TestCase;

class PaymentMethodManagerTest extends TestCase
{
    #[Test]
    public function it_returns_config_and_provider_by_name(): void
    {
        // config
        $config = new BtcPaymentMethodConfig;

        // provider (реальный, не mock)
        $provider = new TestBtcProvider;

        $manager = new PaymentMethodManager(
            configs: [$config],
            providers: [$provider],
        );

        $this->assertSame($config, $manager->getConfig('btc'));
        $this->assertSame($provider, $manager->getProvider('btc'));
    }

    #[Test]
    public function it_throws_exception_if_config_not_found(): void
    {
        $manager = new PaymentMethodManager(
            configs: [],
            providers: [],
        );

        $this->expectException(PaymentMethodNotFoundException::class);
        $this->expectExceptionMessage('Payment method "not_exists" not found.');

        $manager->getConfig('not_exists');
    }

    #[Test]
    public function it_throws_exception_if_provider_not_found(): void
    {
        $config = new BtcPaymentMethodConfig;

        $manager = new PaymentMethodManager(
            configs: [$config],
            providers: [],
        );

        $this->expectException(PaymentMethodNotFoundException::class);
        $this->expectExceptionMessage('Payment method "btc" not found.');

        $manager->getProvider('btc');
    }
}
