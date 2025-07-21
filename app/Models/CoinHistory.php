<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'novel_id',
        'volume_id',
        'chapter_id',
        'status',
        'coin_amount',
        'description',
        'purchased_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    public function volume()
    {
        return $this->belongsTo(Volume::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
