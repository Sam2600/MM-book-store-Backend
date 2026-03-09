<?php

namespace App\Repositories\Rating;

use App\Models\Rating;
use App\Interfaces\Rating\RatingRepositoryInterface;

class RatingRepository implements RatingRepositoryInterface
{
   public function checkRatingExists(int|string $novelId, int|string $userId): bool
   {
      return Rating::where('novel_id', $novelId)
         ->where('user_id', $userId)
         ->exists();
   }
}
