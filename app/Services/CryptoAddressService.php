<?php

namespace App\Services;

use App\Contracts\BtcClientInterface;
use App\Enums\Deposit\CryptoAddressStatus;
use App\Models\CryptoAddress;

class CryptoAddressService
{
    public function __construct(
        private readonly BtcClientInterface $btcClient,
    ) {}

    public function allocateAddress(string $blockchain): CryptoAddress
    {
        return CryptoAddress::create([
            'blockchain' => $blockchain,
            'address' => $this->generateAddress($blockchain),
            'status' => CryptoAddressStatus::USED,
            'payment_target_id' => null,
        ]);
    }

    private function generateAddress(string $blockchain): string
    {
        return match ($blockchain) {
            'bitcoin' => $this->btcClient->createAddress(),
            default => throw new \InvalidArgumentException("Unsupported blockchain: {$blockchain}"),
        };
    }
}
