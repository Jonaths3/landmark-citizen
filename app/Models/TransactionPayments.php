<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPayments extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'vnuban',
        'tranx_ref',
        'payment_ref',
        'cust_email',
        'merchant_amount',
        'fee',
        'amount_payable',
        'amount_paid',
        'status',
        'sales_rent',
    ];
}
