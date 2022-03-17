<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemedAmount extends Model
{
    use HasFactory;
    protected $fillable = [
        'tranx_id',
        'citizen_id',
        'redeemed_amount',
    ];
}
