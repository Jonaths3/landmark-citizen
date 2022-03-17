<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'from',
        'to',
        'transaction_type',
        'transaction_mode',
        'description',
        'amount',
        'transaction_title',
        'narration',
    ];
}
