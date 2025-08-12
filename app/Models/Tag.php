<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'description'
    ];

    public function novels()
    {
        return $this->belongsToMany(Novel::class);
    }
}
