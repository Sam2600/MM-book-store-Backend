<?php

namespace App\Repositories\User;

use App\Models\Novel;
use App\Models\User;
use App\Interfaces\User\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {

   public function getNovelDetailsByAuthor(int|string $userId)
   {
      $now = now();
      $monthStart = now()->startOfMonth();

      return Novel::query()
         ->select(['id', 'title', 'cover_image'])
         ->where('translator_id', $userId)
         ->withSum([
               'coinHistories AS total_coins' => function ($query) use ($monthStart, $now) {
                  $query->where('status', 'earned')
                     ->whereBetween('coin_histories.created_at', [$monthStart, $now]);
               }
            ], 'coin_amount')
         ->with('volumes.chapters')
         ->with('categories:id,name')
         ->withCount('bookmarks')
         ->get();
   }

   public function getAuthorInfoById(int|string $id)
   {
      return User::find($id);
   }

   public function getAuthorInfoAndNovels(int|string $userId)
   {
      return User::select('id', 'name', 'email', 'created_at')
         ->with([
               'novels.categories' => function ($query) {
                  $query->select('categories.id', 'name');
               }
         ])
         ->where('id', $userId)
         ->first();
   }
   
}