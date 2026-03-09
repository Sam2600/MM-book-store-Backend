<?php

namespace App\Repositories\Chapter;

use App\Models\Chapter;
use App\Models\UserChapter;
use App\Interfaces\Chapter\ChapterRepositoryInterface;

class ChapterRepository implements ChapterRepositoryInterface
{
   public function getChatperByIds(int|String $volumeId, int|String $chapterId)
   {
      return Chapter::where("volume_id", $volumeId)
         ->where("chapter_number", $chapterId)
         ->first();
   }

   public function getChapterDetailsById(int|string $chapterId)
   {
      return Chapter::query()
         ->select(['id', 'volume_id', 'chapter_number', 'title', 'content'])
         ->with('volume:id,volume_title,volume_number')
         ->where('id', '=', $chapterId)
         ->first(); // better than get() for single record
   }

   public function isChapterUnlocked(int|string $userId, int|string $chapterId): bool
   {
      return UserChapter::where('user_id', $userId)
         ->where('chapter_id', $chapterId)
         ->exists();
   }
}