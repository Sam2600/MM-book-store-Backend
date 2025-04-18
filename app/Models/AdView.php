<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdView extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ad_provider', 'reward_amount', 'viewed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
