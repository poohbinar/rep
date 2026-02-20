<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *      path="/api/v1/finance/deposit/{publicId}/status",
 *      security={{"bearerAuth":{}}},
 *      summary="STEP 4. Get crypto deposit status",
 *      tags={"Finance"},
 *
 *      @OA\Parameter(
 *          name="publicId",
 *          in="path",
 *          required=true,
 *          description="Public deposit UUID",
 *
 *          @OA\Schema(
 *              type="string",
 *              example="6e8f4e02-c91c-465f-b22d-7f102fca381b"
 *          )
 *      ),
 *
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *
 *          @OA\JsonContent(ref="#/components/schemas/DepositInfoSchema")
 *      ),
 *
 *      @OA\Response(
 *          response=404,
 *          description="Deposit not found",
 *
 *          @OA\JsonContent(ref="#/components/schemas/NotFoundException")
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
class DepositStatusActionSchema {}
