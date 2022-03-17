<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'transaction_id',
        'transaction_type',
        'amount',
    ];
}
