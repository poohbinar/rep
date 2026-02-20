<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use App\Enums\Deposit\Blockchain;
use App\Enums\Deposit\DepositStatuses;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="DepositStatusSchema",
 *      type="object",
 *      description="Current state of a crypto deposit",
 *
 *      @OA\Property(
 *          property="public_id",
 *          type="string",
 *          example="6e8f4e02-c91c-465f-b22d-7f102fca381b"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          type="string",
 *          enum=DepositStatuses::AVAILABLE_API_VALUES,
 *          example="awaiting_payment"
 *      ),
 *      @OA\Property(
 *          property="blockchain",
 *          type="string",
 *          enum=Blockchain::AVAILABLE_VALUES,
 *          example="solana"
 *      ),
 *      @OA\Property(
 *          property="expected_crypto_amount",
 *          type="string",
 *          nullable=true,
 *          example="1.25000000"
 *      ),
 *      @OA\Property(
 *          property="usd_amount",
 *          type="string",
 *          nullable=true,
 *          example="125.45000000"
 *      ),
 *      @OA\Property(
 *          property="credited_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true,
 *          example="2026-01-14 13:45:22"
 *      ),
 *      @OA\Property(
 *          property="is_final",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="payment",
 *          ref="#/components/schemas/DepositPaymentSchema",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="qr",
 *          type="string",
 *          nullable=true,
 *          example="solana:8FvYw6...?amount=1.25"
 *      ),
 *      @OA\Property(
 *          property="confirmations",
 *          type="integer",
 *          nullable=true,
 *          example=12
 *      )
 * )
 */
class DepositStatusSchema {}
