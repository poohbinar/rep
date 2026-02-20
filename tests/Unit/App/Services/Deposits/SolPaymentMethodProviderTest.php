<?php

namespace Tests\Unit\App\Services\Deposits;

use App\Contracts\PaymentMethodConfigInterface;
use App\Enums\Deposit\PaymentTargetType;
use App\Models\BufferWallet;
use App\Models\CryptoDeposit;
use App\Services\BufferWalletService;
use App\Services\Deposit\Providers\SolPaymentMethodProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SolPaymentMethodProviderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_payment_target_using_buffer_wallet(): void
    {
        $wallet = BufferWallet::factory()->create([
            'blockchain' => 'solana',
            'address' => '0xABC',
        ]);

        $bufferWalletService = $this->createMock(BufferWalletService::class);
        $bufferWalletService
            ->method('getActiveWallet')
            ->with('solana')
            ->willReturn($wallet);

        $config = $this->createMock(PaymentMethodConfigInterface::class);

        $provider = new SolPaymentMethodProvider(
            $config,
            $bufferWalletService,
        );

        $deposit = CryptoDeposit::factory()->create([
            'wallet_id' => $wallet->id,
            'blockchain' => 'solana',
        ]);

        $paymentTarget = $provider->getPaymentTarget($deposit);

        $this->assertSame('solana', $paymentTarget->blockchain);
        $this->assertSame('0xABC', $paymentTarget->address);
        $this->assertSame(
            PaymentTargetType::BUFFER,
            $paymentTarget->type
        );
    }
}
