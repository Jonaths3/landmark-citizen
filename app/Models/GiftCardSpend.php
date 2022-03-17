<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardSpend extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'card_no',
        'amount_spent',
    ];
}
