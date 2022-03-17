<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;
    protected $fillable = [
        'citizen_id',
        'tranx_id',
        'point_earned',
        'amount_spent',
        'cashback_earned',
    ];
}
