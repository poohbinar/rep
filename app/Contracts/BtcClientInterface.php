<?php

namespace App\Contracts;

interface BtcClientInterface
{
    public function createAddress(): string;
}
