<?php

namespace App\OpenApi\Exception;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="UnprocessableEntityException",
 *      type="object",
 *      description="Validation errors",
 *
 *      @OA\Property(
 *          property="success",
 *          type="boolean",
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Validation failed."
 *      ),
 *      @OA\Property(
 *          property="errors",
 *          type="object",
 *          additionalProperties=@OA\Property(
 *              type="array",
 *
 *              @OA\Items(
 *                  type="string",
 *                  example="The field format is invalid."
 *              )
 *          )
 *      )
 *  )
 */
class UnprocessableEntityException {}
