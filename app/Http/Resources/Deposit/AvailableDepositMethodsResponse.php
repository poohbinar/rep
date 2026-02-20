<?php

namespace App\Http\Resources\Deposit;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailableDepositMethodsResponse extends JsonResource
{
    public function __construct(public $items, public $popular)
    {
        parent::__construct(null);
    }

    public function toArray($request): array
    {
        return [
            'items' => PaymentMethodResource::collection($this->items),
            'popular' => PaymentMethodResource::collection($this->popular),
        ];
    }
}
