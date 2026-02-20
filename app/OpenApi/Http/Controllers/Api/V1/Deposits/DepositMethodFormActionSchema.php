<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *      path="/api/v1/finance/deposit/method/{method}",
 *      security={{"bearerAuth":{}}},
 *      summary="STEP 2. Get deposit method form and limits",
 *      description="Returns dynamic form fields required to create a deposit using selected payment method.",
 *      tags={"Finance"},
 *
 *      @OA\Parameter(
 *          name="method",
 *          in="path",
 *          required=true,
 *          description="Deposit payment method code",
 *
 *          @OA\Schema(type="string", example="sol")
 *      ),
 *
 *      @OA\Response(
 *          response=200,
 *          description="Deposit method form",
 *
 *          @OA\JsonContent(ref="#/components/schemas/DepositFormSchema")
 *      ),
 *
 *      @OA\Response(
 *          response=404,
 *          description="Payment method not found"
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized",
 *
 *          @OA\JsonContent(ref="#/components/schemas/Unauthorized")
 *      )
 * )
 */
class DepositMethodFormActionSchema {}
