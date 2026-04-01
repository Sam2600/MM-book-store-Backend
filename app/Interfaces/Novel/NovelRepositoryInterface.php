<?php

namespace App\Interfaces\Novel;

interface NovelRepositoryInterface 
{
   public function getNovels();

   public function getPopularThisWeekNovels();

   public function getPopularAllTimeNovels();

   public function getPopularThisMonthNovels();

   public function getLatestNovels();

   public function getNovelsByAuthor();

   public function getNovelDetailInfoById(int $id, int $user_id);

   public function getNovelById(int|String $id);

   public function getBookMarks(int|string $user_id);

   public function getNovelByBookMarks(array $novel_ids);

   public function getCurrentUserNovelChapters(int|string $novelId, int|string $userId);

   public function getLatestUpdatedNovels();
}