<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'volume_id',
        'title',
        'content',
        'status'
    ];

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    public function volume()
    {
        return $this->belongsTo(Volume::class);
    }

    public function chapterPurchases()
    {
        return $this->hasMany(ChapterPurchase::class);
    }

    public function authorEarnings()
    {
        return $this->hasMany(AuthorEarning::class);
    }
}
