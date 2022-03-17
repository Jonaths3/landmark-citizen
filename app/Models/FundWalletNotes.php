<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundWalletNotes extends Model
{
    use HasFactory;
    protected $fillable =[
        'notes'
    ];
}
