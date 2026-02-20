<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected int $status;

    public function __construct(
        string $message,
        int $status = 400
    ) {
        parent::__construct($message, $status);
        $this->status = $status;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'code' => $this->status,
        ], $this->status);
    }
}
