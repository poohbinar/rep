<?php

namespace App\Services\Deposit;

use App\Contracts\SolClientInterface;
use Illuminate\Support\Facades\Http;

class SolRpcClient implements SolClientInterface
{
    public function createAddress(): string
    {
        $result = $this->post(
            config('services.nownodes.sol_create_address_method', 'createAddress')
        );

        if (is_string($result) && $result !== '') {
            return $result;
        }

        if (is_array($result)) {
            $address = $result['address'] ?? $result['pubkey'] ?? null;
            if (is_string($address) && $address !== '') {
                return $address;
            }
        }

        throw new \RuntimeException('NOWNodes SOL createAddress returned invalid response.');
    }

    public function post(string $method, array $params = []): mixed
    {
        return Http::withHeaders([
            'X-API-Key' => config('services.nownodes.api_key'),
        ])->post(
            config('services.nownodes.sol_url', 'https://solana.nownodes.io'),
            [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => $method,
                'params' => $params,
            ]
        )->json('result');
    }

    public function getSignaturesForAddress(string $address, int $limit = 20): array
    {
        return (array) $this->post('getSignaturesForAddress', [
            $address,
            ['limit' => $limit],
        ]);
    }

    public function getTransaction(string $signature): array
    {
        return (array) $this->post('getTransaction', [
            $signature,
            'jsonParsed',
        ]);
    }
}
