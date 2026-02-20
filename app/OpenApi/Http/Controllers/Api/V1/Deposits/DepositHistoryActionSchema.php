<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use App\Enums\Deposit\SortDirections;
use App\Enums\Deposit\SortFields;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *      path="/api/v1/finance/deposit/history",
 *      security={{"bearerAuth":{}}},
 *      summary="Get user crypto deposit history",
 *      tags={"Finance"},
 *
 *      @OA\Parameter(
 *          name="sort_field",
 *          in="query",
 *          description="Field to sort deposits by",
 *          required=false,
 *
 *          @OA\Schema(
 *              type="string",
 *              enum=SortFields::AVAILABLE_VALUES,
 *              default=SortFields::DEFAULT_VALUE
 *          )
 *      ),
 *
 *      @OA\Parameter(
 *          name="sort",
 *          in="query",
 *          description="Sort direction",
 *          required=false,
 *
 *          @OA\Schema(
 *              type="string",
 *              enum=SortDirections::AVAILABLE_VALUES,
 *              default=SortDirections::DEFAULT_VALUE
 *          )
 *      ),
 *
 *      @OA\Parameter(
 *          name="date_from",
 *          in="query",
 *          description="Deposit credited datetime from (Y-m-d H:i:s)",
 *          required=false,
 *
 *          @OA\Schema(
 *              type="string",
 *              format="date-time",
 *              example="2026-01-01 00:00:00"
 *          ),
 *      ),
 *
 *      @OA\Parameter(
 *          name="date_to",
 *          in="query",
 *          description="Deposit credited datetime to (Y-m-d H:i:s)",
 *          required=false,
 *
 *          @OA\Schema(
 *              type="string",
 *              format="date-time",
 *              example="2026-01-31 23:59:59"
 *          ),
 *      ),
 *
 *      @OA\Parameter(
 *          name="per_page",
 *          in="query",
 *          description="Number of items per page",
 *          required=false,
 *
 *          @OA\Schema(
 *              type="integer",
 *              minimum=1,
 *              maximum=100,
 *              default=10
 *          ),
 *      ),
 *
 *      @OA\Parameter(
 *          name="page_number",
 *          in="query",
 *          description="Page number",
 *          required=false,
 *
 *          @OA\Schema(
 *              type="integer",
 *              minimum=1,
 *              default=1
 *          ),
 *      ),
 *
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *
 *          @OA\JsonContent(
 *              type="object",
 *
 *              @OA\Property(
 *                  property="items",
 *                  type="array",
 *
 *                  @OA\Items(ref = "#/components/schemas/DepositInfoSchema")
 *              ),
 *
 *              @OA\Property(
 *                  property="meta",
 *                  ref="#/components/schemas/Meta"
 *              )
 *          )
 *      ),
 *
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized",
 *
 *          @OA\JsonContent(ref="#/components/schemas/Unauthorized")
 *      ),
 *
 *      @OA\Response(
 *           response=422,
 *           description="Validation errors",
 *
 *           @OA\JsonContent(ref="#/components/schemas/UnprocessableEntityException")
 *       )
 *  )
 */
class DepositHistoryActionSchema {}
