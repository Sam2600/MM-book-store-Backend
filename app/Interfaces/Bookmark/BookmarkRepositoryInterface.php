<?php

namespace App\Interfaces\Bookmark;

interface BookmarkRepositoryInterface
{
   public function checkBookmarkExists(int|string $novelId, int|string $userId): bool;
}