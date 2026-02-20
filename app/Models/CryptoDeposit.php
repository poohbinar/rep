<?php

namespace App\Models;

use App\Enums\Deposit\DepositStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CryptoDeposit extends Model
{
    use HasFactory;

    protected $table = 'crypto_deposits';

    protected $connection = 'mysql';

    public $timestamps = false;

    protected $fillable = [
        'public_id',
        'user_id',
        'wallet_id',
        'payment_method',
        'blockchain',
        'currency',
        'decimals',
        'expected_crypto_amount',
        'status',
        'usd_amount',
        'credited_at',
    ];

    protected $casts = [
        'status' => DepositStatuses::class,
        'credited_at' => 'datetime',
    ];

    public function paymentTarget(): HasOne
    {
        return $this->hasOne(PaymentTarget::class);
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            CryptoTransaction::class,
            PaymentTarget::class,
            'crypto_deposit_id',
            'payment_target_id',
            'id',
            'id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
