<?php

namespace App\Exceptions\Deposit;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class DepositNotFoundException extends ApiException
{
    public function __construct(string $message = 'Deposit not found.')
    {
        parent::__construct(
            $message,
            Response::HTTP_NOT_FOUND
        );
    }
}
