<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Novel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'translator_id',
        'title',
        'original_author_name',
        'original_book_name',
        'description',
        'cover_image',
        'view_count',
        'status',
    ];

    public function translator()
    {
        return $this->belongsTo(User::class, 'translator_id');
    }

    public function chapters()
    {
        return $this->hasManyThrough(
            Chapter::class,   // Final model
            Volume::class,    // Intermediate model
            'novel_id',       // Foreign key on volumes table
            'volume_id',      // Foreign key on chapters table
            'id',             // Local key on novels table
            'id'              // Local key on volumes table
        );
    }

    public function volumes()
    {
        return $this->hasMany(Volume::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_novel');
    }

    public function views()
    {
        return $this->hasMany(NovelView::class);
    }

    public function coinHistories()
    {
        return $this->hasMany(CoinHistory::class, 'novel_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'novel_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'novel_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
