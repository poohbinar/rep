<?php

namespace Tests\Unit\App\Services\Deposits;

use App\Services\Deposit\BtcRpcClient;
use App\Services\Deposit\EthRpcClient;
use App\Services\Deposit\SolRpcClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class RpcClientsTest extends TestCase
{
    #[Test]
    public function btc_rpc_client_returns_generated_address(): void
    {
        Http::fake([
            '*' => Http::response(['result' => 'bc1qgenerated00000000000000000000000000001'], 200),
        ]);

        $client = new BtcRpcClient;

        $this->assertSame('bc1qgenerated00000000000000000000000000001', $client->createAddress());
    }

    #[Test]
    public function btc_rpc_client_throws_when_response_is_invalid(): void
    {
        Http::fake([
            '*' => Http::response(['result' => null], 200),
        ]);

        $client = new BtcRpcClient;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NOWNodes BTC createAddress returned invalid response.');

        $client->createAddress();
    }

    #[Test]
    public function eth_rpc_client_returns_generated_address(): void
    {
        Http::fake([
            '*' => Http::response(['result' => '0xbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'], 200),
        ]);

        $client = new EthRpcClient;

        $this->assertSame('0xbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', $client->createAddress());
    }

    #[Test]
    public function eth_rpc_client_throws_when_response_is_invalid(): void
    {
        Http::fake([
            '*' => Http::response(['result' => ['address' => 'invalid']], 200),
        ]);

        $client = new EthRpcClient;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NOWNodes ETH createAddress returned invalid response.');

        $client->createAddress();
    }

    #[Test]
    public function sol_rpc_client_accepts_string_result(): void
    {
        Http::fake([
            '*' => Http::response(['result' => 'SoLAddress1111111111111111111111111111111111111'], 200),
        ]);

        $client = new SolRpcClient;

        $this->assertSame('SoLAddress1111111111111111111111111111111111111', $client->createAddress());
    }

    #[Test]
    public function sol_rpc_client_accepts_object_like_result_with_address_field(): void
    {
        Http::fake([
            '*' => Http::response(['result' => ['address' => 'SoLObjectAddress111111111111111111111111111111']], 200),
        ]);

        $client = new SolRpcClient;

        $this->assertSame('SoLObjectAddress111111111111111111111111111111', $client->createAddress());
    }

    #[Test]
    public function sol_rpc_client_throws_when_response_is_invalid(): void
    {
        Http::fake([
            '*' => Http::response(['result' => []], 200),
        ]);

        $client = new SolRpcClient;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NOWNodes SOL createAddress returned invalid response.');

        $client->createAddress();
    }
}
