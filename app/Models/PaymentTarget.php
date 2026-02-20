<?php

namespace App\Models;

use App\Enums\Deposit\PaymentTargetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTarget extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'crypto_deposit_id',
        'type',
        'blockchain',
        'address',
        'memo',
        'expires_at',
    ];

    protected $casts = [
        'type' => PaymentTargetType::class,
    ];

    public function deposit()
    {
        return $this->belongsTo(CryptoDeposit::class, 'crypto_deposit_id');
    }
}
