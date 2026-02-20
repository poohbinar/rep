<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoAddress extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mysql';

    protected $table = 'crypto_addresses';

    protected $fillable = [
        'blockchain',
        'address',
        'payment_target_id',
        'status',
    ];

    public function cryptoDeposit(): BelongsTo
    {
        return $this->belongsTo(CryptoDeposit::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            CryptoTransaction::class,
            'crypto_address_id'
        );
    }
}
