<?php

namespace App\Interfaces\Chapter;

interface ChapterRepositoryInterface
{
   public function getChatperByIds(int|String $volumeId, int|String $chapterId);

   public function getChapterDetailsById(int|string $chapterId);

   public function isChapterUnlocked(int|string $userId, int|string $chapterId): bool;
}