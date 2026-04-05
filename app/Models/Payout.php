<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'translator_id',
        'period',
        'total_amount',
        'payment_method',
        'payment_account',
        'status',
        'reference_number',
        'note',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at'      => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'translator_id');
    }
}
