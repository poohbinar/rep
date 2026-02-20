<?php

namespace App\Exceptions\Deposit;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class InvalidDepositAmountException extends ApiException
{
    public function __construct(string $message = 'Invalid deposit amount')
    {
        parent::__construct($message, Response::HTTP_FORBIDDEN);
    }
}
