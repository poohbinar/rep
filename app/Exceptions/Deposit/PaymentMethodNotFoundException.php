<?php

namespace App\Exceptions\Deposit;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class PaymentMethodNotFoundException extends ApiException
{
    public function __construct(string $method)
    {
        parent::__construct(
            sprintf('Payment method "%s" not found.', $method),
            Response::HTTP_NOT_FOUND
        );
    }
}
