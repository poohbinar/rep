<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use App\Enums\Deposit\Blockchain;
use App\Enums\Deposit\PaymentMethodEnum;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="DepositFormSchema",
 *      type="object",
 *
 *      @OA\Property(
 *          property="method",
 *          type="string",
 *          enum=PaymentMethodEnum::AVAILABLE_VALUES,
 *          example="sol"
 *      ),
 *      @OA\Property(
 *          property="blockchain",
 *          type="string",
 *          enum=Blockchain::AVAILABLE_VALUES,
 *          nullable=true,
 *          example="solana"
 *      ),
 *      @OA\Property(
 *          property="currency",
 *          type="string",
 *          example="SOL",
 *          description="Crypto or fiat currency code"
 *      ),
 *      @OA\Property(
 *          property="limits",
 *          type="object",
 *          @OA\Property(property="min", type="number", example=0.01),
 *          @OA\Property(property="max", type="number", example=100)
 *      ),
 *      @OA\Property(
 *          property="fields",
 *          type="array",
 *          description="Dynamic fields that must be sent in STEP 3 request (fields object)",
 *
 *          @OA\Items(ref="#/components/schemas/DepositFormFieldSchema")
 *      ),
 *
 *      @OA\Property(
 *          property="promo_available",
 *          type="boolean",
 *          example=true,
 *          description="Indicates whether promo code can be used with this method"
 *      )
 * )
 */
class DepositFormSchema {}
