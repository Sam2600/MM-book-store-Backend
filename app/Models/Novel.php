<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novel extends Model
{
    use HasFactory;

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
        return $this->hasMany(Chapter::class);
    }

    public function volumes()
    {
        return $this->hasMany(Volume::class)->orderBy('order');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_novel');
    }

    public function views() { 
        return $this->hasMany(NovelView::class); 
    }
}
