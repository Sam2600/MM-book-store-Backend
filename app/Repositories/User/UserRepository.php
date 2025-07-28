<?php

namespace App\Repositories\User;

use App\Models\Novel;
use App\Interfaces\User\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {

   public function getNovelDetailsByAuthor(int|string $userId)
   {
      return Novel::with('categories:id,name')->withCount('bookmarks')->withCount('chapters')->where('translator_id', $userId)->get();
   }
}