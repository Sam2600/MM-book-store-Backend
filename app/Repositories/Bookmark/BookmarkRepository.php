<?php

namespace App\Repositories\Bookmark;

use App\Models\Bookmark;
use App\Interfaces\Bookmark\BookmarkRepositoryInterface;

class BookmarkRepository implements BookmarkRepositoryInterface
{
   public function checkBookmarkExists(int|string $novelId, int|string $userId): bool
   {
      return Bookmark::where('novel_id', $novelId)
         ->where('user_id', $userId)
         ->onlyTrashed()
         ->exists();
   }
}
