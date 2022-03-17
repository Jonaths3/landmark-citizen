<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransactions extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'cashier_id',
        'transaction_id',
        'amount',
        'status',
        'narration',
        'tranx_value',
    ];
}
