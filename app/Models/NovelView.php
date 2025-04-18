<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NovelView extends Model
{
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'user_id',
        'ip_address',
    ];

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
