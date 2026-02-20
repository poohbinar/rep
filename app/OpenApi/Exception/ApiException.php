<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 *  @OA\Schema(
 *      schema="ApiException",
 *      type="object",
 *      description="Api exception object",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Unable to cancel the promo code TEST141"
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="integer",
 *          example=409
 *      )
 *  )
 */
class ApiException {}
