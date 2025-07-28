<?php

namespace App\Interfaces\User;

interface UserRepositoryInterface {

   public function getNovelDetailsByAuthor(int|string $userId);
}