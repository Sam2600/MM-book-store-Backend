<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volume extends Model
{
    use HasFactory;

    protected $fillable = ['novel_id', 'volume_number', 'volume_title', 'order'];

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('created_at');
    }
}
