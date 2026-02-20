<?php

namespace App\Services\Deposit;

use App\Contracts\EthClientInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EthRpcClient implements EthClientInterface
{
    public function createAddress(): string
    {
        $result = Http::withHeaders([
            'X-API-Key' => config('services.nownodes.api_key'),
        ])->post(
            config('services.nownodes.eth_url', 'https://eth.nownodes.io'),
            [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'personal_newAccount',
                'params' => [config('services.nownodes.eth_address_passphrase', '')],
            ]
        )->json('result');

        if (! is_string($result) || $result === '') {
            throw new RuntimeException('NOWNodes ETH createAddress returned invalid response.');
        }

        return $result;
    }
}
