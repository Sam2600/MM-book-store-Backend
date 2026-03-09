<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserChapter extends Model
{
   use HasFactory;

   /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   protected $fillable = [
      'user_id', 'novel_id', 'volume_id', 'chapter_id',
   ];

   public function user()
   {
      return $this->belongsTo(User::class);
   }

   public function novel()
   {
      return $this->belongsTo(Novel::class);
   }

   public function volume()
   {
      return $this->belongsTo(Volume::class);
   }

   public function chapter()
   {
      return $this->belongsTo(Chapter::class);
   }

}
