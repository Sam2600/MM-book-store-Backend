<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthorEarning extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'translator_id', 'novel_id', 'chapter_id', 'rate_id', 'coins_earned', 'amount', 'source', 'period', 'earned_at'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'translator_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
