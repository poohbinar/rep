<?php

namespace App\Http\Resources\Deposit;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'name' => $this->resource->name,
            'symbol' => $this->resource->symbol,
            'is_active' => $this->resource->is_active,
        ];
    }
}
