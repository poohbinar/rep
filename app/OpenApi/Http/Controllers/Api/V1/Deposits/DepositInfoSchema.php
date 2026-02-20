<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use App\Enums\Deposit\Blockchain;
use App\Enums\Deposit\DepositStatuses;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="DepositInfoSchema",
 *      type="object",
 *
 *      @OA\Property(
 *          property="public_id",
 *          type="string",
 *          example="6e8f4e02-c91c-465f-b22d-7f102fca381b",
 *          description="Public deposit UUID"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          type="string",
 *          enum=DepositStatuses::AVAILABLE_API_VALUES,
 *          example="credited",
 *          description="Deposit status (new, awaiting_payment, tx_detected, credited, rejected)"
 *      ),
 *      @OA\Property(
 *          property="blockchain",
 *          type="string",
 *          enum=Blockchain::AVAILABLE_VALUES,
 *          example="solana",
 *          description="Blockchain network used for the deposit"
 *      ),
 *      @OA\Property(
 *          property="expected_crypto_amount",
 *          type="string",
 *          nullable=true,
 *          example="1.25000000",
 *          description="Expected crypto amount from the user"
 *      ),
 *      @OA\Property(
 *          property="usd_amount",
 *          type="string",
 *          nullable=true,
 *          example="124.66000000",
 *          description="Final credited amount in USD"
 *      ),
 *      @OA\Property(
 *          property="credited_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true,
 *          example="2026-01-14 12:45:10",
 *          description="Date when the deposit was credited to the wallet"
 *      ),
 *      @OA\Property(
 *          property="is_final",
 *          type="boolean",
 *          example=true,
 *          description="Indicates whether the deposit has reached a final state"
 *      ),
 *      @OA\Property(
 *          property="payment",
 *          ref="#/components/schemas/DepositPaymentSchema",
 *          nullable=true,
 *          description="Returned only when deposit status = awaiting_payment"
 *      ),
 *      @OA\Property(
 *          property="qr",
 *          type="string",
 *          nullable=true,
 *          description="Returned only when deposit status = awaiting_payment"
 *      ),
 *      @OA\Property(
 *          property="confirmations",
 *          type="integer",
 *          nullable=true,
 *          example=12,
 *          description="Returned when blockchain transaction is detected"
 *      )
 *  )
 */
class DepositInfoSchema {}
