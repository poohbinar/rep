<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 *  @OA\Schema(
 *      schema="NotFoundException",
 *      type="object",
 *      description="Not found exception object",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Promocode was not found"
 *      ),
 *      @OA\Property(
 *          property="code",
 *          type="integer",
 *          example=404
 *      )
 *  )
 */
class NotFoundException {}
