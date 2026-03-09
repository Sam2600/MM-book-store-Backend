<?php

namespace App\Repositories\User;

use App\Models\Novel;
use App\Models\User;
use App\Interfaces\User\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {

   public function getNovelDetailsByAuthor(int|string $userId)
   {
      // return Novel::with('categories:id,name')->withCount('bookmarks')->withCount('chapters')->where('translator_id', $userId)->get();
      $monthStart = now()->startOfMonth();
      $now = now();

      return Novel::query()
         ->select(['id', 'title', 'cover_image'])
         ->where('translator_id', $userId)
         ->withSum([
               'coinHistories as total_coins_earned' => function ($query) use ($monthStart, $now) {
                  $query->where('status', 'earned')
                     ->whereBetween('coin_histories.created_at', [$monthStart, $now]);
               }
            ], 'coin_amount')
         ->with([
               'chapters' => function ($query) {
                  $query->select(['chapters.id', 'chapters.chapter_number', 'chapters.title', 'chapters.updated_at']);
               }
         ])
         ->with('categories:id,name')
         ->withCount('bookmarks')
         ->get()
         ->map(function ($novel) {
               return [
                  'id' => $novel->id,
                  'title' => $novel->title,
                  'cover_image' => $novel->cover_image,
                  'total_coins_that_is_earned_from_that_novel' => (int) ($novel->total_coins_earned ?? 0),
                  'chapters' => $novel->chapters->map(function ($chapter) {
                     return [
                           'id' => $chapter->id,
                           'chapter_num' => $chapter->chapter_number,
                           'title' => $chapter->title,
                           'updated_date' => optional($chapter->updated_at)->toDateTimeString(),
                     ];
                  })->values(),
               ];
         })->values();
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