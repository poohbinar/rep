<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BufferWallet extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'blockchain',
        'address',
        'label',
        'is_active',
    ];
}
