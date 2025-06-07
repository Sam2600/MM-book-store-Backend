<?php

namespace App\Interfaces\Novel;

interface NovelRepositoryInterface 
{
   public function getNovels();

   public function getPopularThisWeekNovels();

   public function getPopularAllTimeNovels();

   public function getPopularThisMonthNovels();

   public function getLatestNovels();

   public function getCategories();

   public function getNovelsByAuthor();

   public function getNovelDetailInfoById(int $id);

   public function getNovelById(int|String $id);
}