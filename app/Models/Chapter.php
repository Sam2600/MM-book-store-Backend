<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'volume_id',
        'chapter_number',
        'title',
        'content',
        'file_path',
        'coin_cost',
        'status'
    ];

    public function volume()
    {
        return $this->belongsTo(Volume::class);
    }

    public function authorEarnings()
    {
        return $this->hasMany(AuthorEarning::class, 'translator_id');
    }

    public function coinHistories()
    {
        return $this->hasMany(CoinHistory::class, 'chapter_id');
    }
}
