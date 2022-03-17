<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayouts extends Model
{
    use HasFactory;
    protected $fillable = [
        'wallet_id',
        'vendor_id',
        'amount',
        'status',
        'time_in',
        'time_out',
        'ref',
        'transaction_fee',
        'transaction_date'
    ];
}
