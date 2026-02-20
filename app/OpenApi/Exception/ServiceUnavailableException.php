<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="ServiceUnavailableException",
 *      type="object",
 *      title="Error 503 - Service Unavailable",
 *      description="Service is temporarily unavailable due to maintenance or overload",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Service Unavailable."
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="integer",
 *          example=503
 *      ),
 *      @OA\Property(
 *          property="retry_after",
 *          type="integer",
 *          nullable=true,
 *          description="Seconds to wait before retrying",
 *          example=60
 *      )
 * )
 */
class ServiceUnavailableException {}
