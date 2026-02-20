<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="ConflictException",
 *      type="object",
 *      description="Conflict",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Email address already verified."
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="integer",
 *          example=409
 *     )
 * )
 */
class ConflictException {}
