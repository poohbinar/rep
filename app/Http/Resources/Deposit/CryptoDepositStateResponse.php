<?php

namespace App\Http\Resources\Deposit;

use App\Enums\Deposit\DepositStatuses;
use Illuminate\Http\Resources\Json\JsonResource;

class CryptoDepositStateResponse extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'status' => $this->resource->status->api(),
            'blockchain' => $this->resource->blockchain,

            'expected_crypto_amount' => $this->resource->expected_crypto_amount,

            'usd_amount' => $this->resource->usd_amount,
            'credited_at' => $this->resource->credited_at,

            'is_final' => in_array($this->resource->status, [
                DepositStatuses::CREDITED,
                DepositStatuses::REJECTED,
            ], true),

            'payment' => $this->when(
                $this->shouldShowPaymentInstruction(),
                fn () => [
                    'address' => $this->resource->paymentTarget->address,
                    'memo' => $this->resource->paymentTarget->memo,
                    'expires_at' => $this->resource->paymentTarget->expires_at,
                    'type' => $this->resource->paymentTarget->type,
                ]
            ),

            'qr' => $this->when(
                $this->shouldShowPaymentInstruction(),
                fn () => $this->resource->payment_instruction
            ),

            'confirmations' => $this->when(
                $this->resource->relationLoaded('transactions'),
                fn () => $this->resource->transactions->max('confirmations')
            ),
        ];
    }

    private function shouldShowPaymentInstruction(): bool
    {
        return $this->resource->status === DepositStatuses::AWAITING_PAYMENT
            && $this->resource->relationLoaded('paymentTarget')
            && $this->resource->paymentTarget !== null;
    }
}
