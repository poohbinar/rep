<?php

namespace Tests\Unit\App\Services;

use App\Contracts\BtcClientInterface;
use App\Enums\Deposit\CryptoAddressStatus;
use App\Services\CryptoAddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CryptoAddressServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allocates_btc_address_and_marks_it_used(): void
    {
        $btcClient = $this->createMock(BtcClientInterface::class);
        $btcClient->expects($this->once())
            ->method('createAddress')
            ->willReturn('bc1qunitaddress000000000000000000000000000001');

        $service = new CryptoAddressService($btcClient);

        $address = $service->allocateAddress('bitcoin');

        $this->assertSame('bitcoin', $address->blockchain);
        $this->assertSame('bc1qunitaddress000000000000000000000000000001', $address->address);
        $this->assertSame(CryptoAddressStatus::USED, $address->status);
        $this->assertNull($address->payment_target_id);
    }

    #[Test]
    public function it_throws_for_unsupported_blockchain(): void
    {
        $btcClient = $this->createMock(BtcClientInterface::class);
        $btcClient->expects($this->never())->method('createAddress');

        $service = new CryptoAddressService($btcClient);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported blockchain: ethereum');

        $service->allocateAddress('ethereum');
    }
}
