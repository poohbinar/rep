<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="InternalServerErrorException",
 *      type="object",
 *      title="Error 500 - Internal Server Error",
 *      description="An unexpected error occurred on the server",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Internal Server Error."
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="integer",
 *          example=500
 *     ),
 *     @OA\Property(
 *          property="error",
 *          type="string",
 *          nullable=true,
 *          description="Technical error information (development only)",
 *          example="Division by zero in /app/Services/Service.php:125"
 *      )
 * )
 */
class InternalServerErrorException {}
