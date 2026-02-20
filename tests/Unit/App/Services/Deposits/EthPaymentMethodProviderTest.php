<?php

namespace Tests\Unit\App\Services\Deposits;

use App\Contracts\PaymentMethodConfigInterface;
use App\Enums\Deposit\PaymentTargetType;
use App\Models\BufferWallet;
use App\Models\CryptoDeposit;
use App\Services\BufferWalletService;
use App\Services\Deposit\Providers\EthPaymentMethodProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EthPaymentMethodProviderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_payment_target_using_buffer_wallet(): void
    {
        $wallet = BufferWallet::factory()->create([
            'blockchain' => 'ethereum',
            'address' => '0xABC',
        ]);

        $bufferWalletService = $this->createMock(BufferWalletService::class);
        $bufferWalletService
            ->method('getActiveWallet')
            ->with('ethereum')
            ->willReturn($wallet);

        $config = $this->createMock(PaymentMethodConfigInterface::class);

        $provider = new EthPaymentMethodProvider(
            $config,
            $bufferWalletService,
        );

        $deposit = CryptoDeposit::factory()->create([
            'wallet_id' => $wallet->id,
            'blockchain' => 'ethereum',
        ]);

        $paymentTarget = $provider->getPaymentTarget($deposit);

        $this->assertSame('ethereum', $paymentTarget->blockchain);
        $this->assertSame('0xABC', $paymentTarget->address);
        $this->assertSame(
            PaymentTargetType::BUFFER,
            $paymentTarget->type
        );
    }
}
