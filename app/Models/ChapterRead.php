<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'novel_id',
        'user_id',
        'ip_address',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
