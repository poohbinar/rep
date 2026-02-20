<?php

namespace App\Contracts;

interface SolClientInterface
{
    public function createAddress(): string;

    public function getSignaturesForAddress(string $address, int $limit = 20): array;

    public function getTransaction(string $signature): array;
}
