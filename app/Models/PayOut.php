<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayOut extends Model
{
    use HasFactory;
    protected $fillable = [
        'tranx_id',
        'vendor_id',
        'from_account',
        'to_account_no',
        'to_account_name',
        'to_bank',
        'amount'
    ];
}
