<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="ForbiddenException",
 *      type="object",
 *      title="Error 403 - Forbidden",
 *      description="You do not have permission to access this resource",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Forbidden."
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="integer",
 *          example=403
 *     )
 * )
 */
class ForbiddenException {}
