<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="DepositPaymentSchema",
 *      type="object",
 *      description="Payment requisites for awaiting payment deposits",
 *
 *      @OA\Property(
 *          property="address",
 *          type="string",
 *          example="8FvYw6..."
 *      ),
 *      @OA\Property(
 *          property="memo",
 *          type="string",
 *          nullable=true,
 *          example="123456"
 *      ),
 *      @OA\Property(
 *          property="expires_at",
 *          type="string",
 *          format="date-time",
 *          example="2026-01-14 13:00:00"
 *      ),
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          example="address"
 *      )
 * )
 */
class DepositPaymentSchema {}
