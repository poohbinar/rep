<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DepositFormFieldSchema",
 *     type="object",
 *     description="Dynamic deposit form field",
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="amount"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         example="number",
 *         description="Field type (number, string, email, phone, iban, etc)"
 *     ),
 *     @OA\Property(
 *         property="required",
 *         type="boolean",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="decimals",
 *         type="integer",
 *         nullable=true,
 *         example=9,
 *         description="Used for crypto amount fields"
 *     )
 * )
 */
class DepositFormFieldSchema {}
