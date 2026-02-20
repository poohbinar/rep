<?php

namespace App\OpenApi\Http\Controllers\Api\V1\Deposits;

use App\Enums\Deposit\PaymentMethodEnum;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaymentMethodSchema",
 *     type="object",
 *     title="Payment Method",
 *     description="Available deposit payment method",
 *
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Payment method code",
 *         enum=PaymentMethodEnum::AVAILABLE_VALUES,
 *         example="sol"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Human-readable payment method title",
 *         example="Solana (SOL)"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Optional payment method description",
 *         nullable=true,
 *         example="Top up via Solana network"
 *     ),
 *     @OA\Property(
 *         property="priority",
 *         type="integer",
 *         description="Sorting priority (higher = shown first)",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="dev_only",
 *         type="boolean",
 *         description="Available only in development environment",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="enabled",
 *         type="boolean",
 *         description="Whether payment method is enabled",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="allowed_countries",
 *         type="array",
 *         description="List of allowed country codes or '*' for all",
 *
 *         @OA\Items(
 *             type="string",
 *             example="US"
 *         ),
 *         example={"*", "US", "DE"}
 *     ),
 *
 *     @OA\Property(
 *         property="config",
 *         type="object",
 *         description="Payment method limits configuration",
 *         @OA\Property(
 *             property="min_amount",
 *             type="number",
 *             format="float",
 *             description="Minimum deposit amount",
 *             example=0.01
 *         ),
 *         @OA\Property(
 *             property="max_amount",
 *             type="number",
 *             format="float",
 *             description="Maximum deposit amount",
 *             example=1000
 *         )
 *     )
 * )
 */
class PaymentMethodSchema {}
