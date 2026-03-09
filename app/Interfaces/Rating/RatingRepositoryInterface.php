<?php

namespace App\Interfaces\Rating;

interface RatingRepositoryInterface
{
   public function checkRatingExists(int|string $novelId, int|string $userId): bool;
}