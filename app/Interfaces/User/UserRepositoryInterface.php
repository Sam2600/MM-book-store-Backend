<?php

namespace App\Interfaces\User;

interface UserRepositoryInterface {

   public function getNovelDetailsByAuthor(int|string $userId);
   public function getAuthorInfoById(int|string $id);
   public function getAuthorInfoAndNovels(int|string $userId);
}