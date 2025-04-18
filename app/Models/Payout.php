<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = ['author_id', 'total_coins', 'amount', 'status'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
