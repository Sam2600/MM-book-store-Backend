<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'translator_id', 
        'total_coins', 
        'amount', 
        'status'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'translator_id');
    }
}
