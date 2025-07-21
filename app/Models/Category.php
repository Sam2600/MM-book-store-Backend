<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'created_at', 
        'updated_at', 
        'pivot'
    ];

    protected $fillable = ['name'];

    public function novels()
    {
        return $this->belongsToMany(Novel::class, 'category_novel');
    }
}
