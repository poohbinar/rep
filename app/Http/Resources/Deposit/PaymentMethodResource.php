<?php

namespace App\Http\Resources\Deposit;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource;

        return [
            'code' => $resource['code'],
            'title' => $resource['title'],
            'description' => $resource['description'],
            'priority' => $resource['priority'],
            'dev_only' => (bool) $resource['dev_only'],
            'enabled' => (bool) $resource['enabled'],
            'allowed_countries' => $resource['allowed_countries'],
            'config' => [
                'min_amount' => $resource['min_amount'],
                'max_amount' => $resource['max_amount'],
            ],
        ];
    }
}
