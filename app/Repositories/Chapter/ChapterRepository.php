<?php

namespace App\Repositories\Chapter;

use App\Models\Chapter;
use App\Interfaces\Chapter\ChapterRepositoryInterface;

class ChapterRepository implements ChapterRepositoryInterface
{
   public function getChatperByIds(int|String $volumeId, int|String $chapterId)
   {
      return Chapter::where("volume_id", $volumeId)
         ->where("id", $chapterId)
         ->firstOrFail();
   }
}