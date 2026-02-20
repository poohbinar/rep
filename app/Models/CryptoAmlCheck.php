<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoAmlCheck extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mysql';

    protected $table = 'crypto_aml_checks';

    protected $fillable = [
        'provider',
        'blockchain',
        'tx_hash',
        'address',
        'risk_level',
        'risk_score',
        'categories',
        'raw_response',
        'checked_at',
    ];

    protected $casts = [
        'categories' => 'array',
        'raw_response' => 'array',
        'checked_at' => 'datetime',
    ];

    public function isAmlClean(): bool
    {
        return $this->risk_level === 0;
    }
}
