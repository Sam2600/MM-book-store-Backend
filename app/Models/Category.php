<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at', 'pivot'];

    public function novels()
    {
        return $this->belongsToMany(Novel::class, 'category_novel');
    }
}
