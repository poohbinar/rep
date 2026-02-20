<?php

namespace App\OpenApi\Http\Controllers\Api\V1;

use OpenApi\Annotations as OA;

/**
 *  @OA\Schema(
 *      schema="Meta",
 *      type="object",
 *      description="Pagination object",
 *
 *      @OA\Property(
 *          property="current_page",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(
 *          property="per_page",
 *          type="integer",
 *          example=10
 *      ),
 *      @OA\Property(
 *          property="total",
 *          type="integer",
 *          example=5
 *     )
 * )
 */
class MetaSchema {}
