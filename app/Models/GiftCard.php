<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'card_no',
        'card_pin',
        'status',
        'funded_amount',
        'activated_at',
        'activated_by',
        'deactivated_by',
        'created_by',
    ];

    protected $hidden = [
        'card_pin',
    ];
}
