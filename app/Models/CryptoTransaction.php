<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CryptoTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mysql';

    protected $table = 'crypto_transactions';

    protected $fillable = [
        'blockchain',
        'tx_hash',
        'from_address',
        'to_address',
        'memo',
        'amount',
        'amount_raw',
        'confirmations',
        'status',
        'payment_target_id',
        'detected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'detected_at' => 'datetime',
    ];

    public function paymentTarget(): BelongsTo
    {
        return $this->belongsTo(PaymentTarget::class);
    }

    public function amlCheck(): HasOne
    {
        return $this->hasOne(
            CryptoAmlCheck::class,
            'tx_hash',
            'tx_hash'
        );
    }
}
