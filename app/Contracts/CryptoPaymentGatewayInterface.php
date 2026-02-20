<?php

namespace App\Contracts;

use App\Models\CryptoDeposit;
use App\Models\PaymentTarget;

interface CryptoPaymentGatewayInterface
{
    public function createPaymentTarget(CryptoDeposit $deposit): PaymentTarget;

    public function findIncomingTransactions(string $address): array;

    public function getConfirmations(string $signature): int;
}
