<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *      path="/api/v1/finance/deposit/methods",
 *      security={{"bearerAuth":{}}},
 *      summary="STEP 1. Get available deposit methods",
 *      tags={"Finance"},
 *
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *
 *          @OA\JsonContent(
 *              type="object",
 *
 *              @OA\Property(
 *                  property="success",
 *                  type="boolean",
 *                  example=true
 *              ),
 *              @OA\Property(
 *                  property="items",
 *                  type="array",
 *
 *                  @OA\Items(ref="#/components/schemas/PaymentMethodSchema")
 *              ),
 *
 *              @OA\Property(
 *                  property="popular",
 *                  type="array",
 *
 *                  @OA\Items(ref="#/components/schemas/PaymentMethodSchema")
 *              )
 *          )
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
class DepositMethodsActionSchema {}
