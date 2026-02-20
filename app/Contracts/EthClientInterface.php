<?php

namespace App\Contracts;

interface EthClientInterface
{
    public function createAddress(): string;
}
