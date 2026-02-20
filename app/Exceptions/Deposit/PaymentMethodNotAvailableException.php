<?php

namespace App\Exceptions\Deposit;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class PaymentMethodNotAvailableException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
