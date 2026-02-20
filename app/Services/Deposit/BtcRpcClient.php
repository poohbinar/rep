<?php

namespace App\Services\Deposit;

use App\Contracts\BtcClientInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BtcRpcClient implements BtcClientInterface
{
    public function createAddress(): string
    {
        $result = Http::withHeaders([
            'X-API-Key' => config('services.nownodes.api_key'),
        ])->post(
            config('services.nownodes.btc_url', 'https://btcbook.nownodes.io'),
            [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getnewaddress',
                'params' => [],
            ]
        )->json('result');

        if (! is_string($result) || $result === '') {
            throw new RuntimeException('NOWNodes BTC createAddress returned invalid response.');
        }

        return $result;
    }
}
