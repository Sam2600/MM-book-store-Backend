<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Volume extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'novel_id', 
        'volume_number', 
        'volume_title'
    ];

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function coinHistories()
    {
        return $this->hasMany(CoinHistory::class, 'volume_id');
    }
}
