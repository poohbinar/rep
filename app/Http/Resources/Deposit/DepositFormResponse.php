<?php

namespace App\Http\Resources\Deposit;

use Illuminate\Http\Resources\Json\JsonResource;

class DepositFormResponse extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'method' => $this->resource['method'],
            'blockchain' => $this->resource['blockchain'] ?? null,
            'currency' => $this->resource['currency'],
            'limits' => [
                'min' => $this->resource['min_amount'],
                'max' => $this->resource['max_amount'],
            ],
            'fields' => $this->resource['fields'],
            'promo_available' => $this->resource['promo_available'] ?? true,
        ];
    }
}
