<?php

namespace Tests\Unit\App\Services\Deposits;

use App\Contracts\PaymentMethodConfigInterface;
use App\Enums\Deposit\CryptoAddressStatus;
use App\Enums\Deposit\PaymentTargetType;
use App\Models\CryptoAddress;
use App\Models\CryptoDeposit;
use App\Services\CryptoAddressService;
use App\Services\Deposit\Providers\BtcPaymentMethodProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BtcPaymentMethodProviderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_payment_target_using_unique_crypto_address(): void
    {
        $cryptoAddress = CryptoAddress::factory()->create([
            'blockchain' => 'bitcoin',
            'address' => 'bc1qxyz',
            'status' => CryptoAddressStatus::NEW,
        ]);

        $cryptoAddressService = $this->createMock(CryptoAddressService::class);
        $cryptoAddressService
            ->method('allocateAddress')
            ->with('bitcoin')
            ->willReturn($cryptoAddress);

        $config = $this->createMock(PaymentMethodConfigInterface::class);

        $provider = new BtcPaymentMethodProvider(
            $config,
            $cryptoAddressService,
        );

        $deposit = CryptoDeposit::factory()->create([
            'wallet_id' => 1,
            'blockchain' => 'bitcoin',
        ]);

        $paymentTarget = $provider->getPaymentTarget($deposit);

        $this->assertSame('bitcoin', $paymentTarget->blockchain);
        $this->assertSame('bc1qxyz', $paymentTarget->address);
        $this->assertSame(
            PaymentTargetType::ADDRESS,
            $paymentTarget->type
        );
    }
}
