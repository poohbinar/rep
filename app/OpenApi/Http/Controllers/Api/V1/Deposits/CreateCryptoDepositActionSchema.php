<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use App\Enums\Deposit\PaymentMethodEnum;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *      path="/api/v1/finance/deposit/crypto",
 *      security={{"bearerAuth":{}}},
 *      summary="STEP 3. Create crypto deposit",
 *      description="Creates crypto deposit and returns payment instructions (address + payment URI).",
 *      tags={"Finance"},
 *
 *      @OA\RequestBody(
 *          required=true,
 *
 *          @OA\JsonContent(
 *              type="object",
 *              required={"payment_method", "fields"},
 *
 *              @OA\Property(
 *                  property="payment_method",
 *                  type="string",
 *                  enum=PaymentMethodEnum::AVAILABLE_VALUES,
 *                  example="sol",
 *                  description="Payment method code"
 *              ),
 *              @OA\Property(
 *                  property="fields",
 *                  type="object",
 *                  required={"amount"},
 *                  description="Dynamic method fields",
 *                  @OA\Property(
 *                      property="amount",
 *                      type="number",
 *                      format="float",
 *                      example=0.5,
 *                      description="Expected crypto amount user will send"
 *                  )
 *              ),
 *              @OA\Property(
 *                  property="promo_code",
 *                  type="string",
 *                  example="WELCOME10",
 *                  nullable=true,
 *                  description="Optional promo code"
 *              )
 *          )
 *      ),
 *
 *      @OA\Response(
 *          response=200,
 *          description="Deposit created successfully. Returns deposit state and payment instructions.",
 *
 *          @OA\JsonContent(ref="#/components/schemas/DepositInfoSchema")
 *      ),
 *
 *      @OA\Response(
 *          response=422,
 *          description="Validation error",
 *
 *          @OA\JsonContent(ref="#/components/schemas/UnprocessableEntityException")
 *      ),
 *
 *      @OA\Response(
 *          response=404,
 *          description="Payment method not found",
 *
 *          @OA\JsonContent(ref="#/components/schemas/NotFoundException")
 *      ),
 *
 *      @OA\Response(
 *          response=403,
 *          description="Business validation failed",
 *
 *          @OA\JsonContent(ref="#/components/schemas/ApiException")
 *      ),
 *
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized",
 *
 *          @OA\JsonContent(ref="#/components/schemas/Unauthorized")
 *      )
 * )
 */
class CreateCryptoDepositActionSchema {}
