<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="BadRequestException",
 *      type="object",
 *      title="Error 400 - Bad Request",
 *      description="The server cannot process the request due to malformed syntax",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Bad Request."
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="integer",
 *          example=400
 *     )
 * )
 */
class BadRequestException {}
