<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="Unauthorized",
 *      type="object",
 *      description="Unauthorized Response",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Unauthorized."
 *      )
 *  )
 */
class UnauthorizedSchema {}
