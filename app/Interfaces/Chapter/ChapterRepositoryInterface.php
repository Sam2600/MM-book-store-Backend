<?php

namespace App\Interfaces\Chapter;

interface ChapterRepositoryInterface
{
   public function getChatperByIds(int|String $volumeId, int|String $chapterId);
}