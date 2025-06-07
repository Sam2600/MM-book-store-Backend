<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Novel;
use App\Helpers\Helper;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\Novel\NovelRepositoryInterface;

class NovelRepository implements NovelRepositoryInterface
{
   use Helper;

   public function getNovels()
   {
      return Novel::select('id', 'title')->get();
   }

   public function getPopularThisWeekNovels()
   {
      // Get week start and end dates
      $weekStart = Carbon::now()->startOfWeek();
      $weekEnd = Carbon::now()->endOfWeek();

      $popular = Novel::with([
         'categories:id,name' // Load only essential category fields
      ])->withCount([
         'views as view_count' => function ($query) use ($weekStart, $weekEnd) {
            // Count views within current week only
            $query->whereBetween('created_at', [$weekStart, $weekEnd]);
         }
      ])
         ->select('id', 'title', 'status', 'cover_image')
         ->orderByDesc('view_count')
         ->take(10)
         ->get()
         ->makeHidden(['created_at', 'updated_at']);

      return $popular;
   }

   public function getPopularAllTimeNovels()
   {
      return Novel::with('categories')->select('id', 'title', 'description', 'cover_image')->orderByDesc('view_count')->take(5)->get()->makeHidden(['created_at', 'updated_at']);
   }

   public function getPopularThisMonthNovels()
   {
      // Get month start and end dates
      $monthStart = Carbon::now()->startOfMonth();
      $monthEnd = Carbon::now()->endOfMonth();

      $popular = Novel::with([
         'categories:id,name' // Eager load only id and name from categories
      ])->withCount([
         'views as view_count' => function ($query) use ($monthStart, $monthEnd) {
            // Count views within current month only
            $query->whereBetween('created_at', [$monthStart, $monthEnd]);
         }
      ])
         ->select('id', 'title', 'status', 'cover_image')
         ->orderByDesc('view_count')
         ->take(10)
         ->get()
         ->makeHidden(['created_at', 'updated_at']);

      return $popular;
   }

   public function getLatestNovels()
   {
      return Novel::select('id', 'title', 'description', 'created_at')->orderBy('created_at', 'desc')->take(5)->get();
   }

   public function getCategories()
   {
      return Category::select('id', 'name')->get();
   }

   public function getNovelsByAuthor()
   {
      // Get currently authenticated user
      $user = Auth::user();

      // Get novels where user is translator
      return Novel::where('translator_id', $user->id)->select('id', 'title')->get();
   }

   public function getNovelDetailInfoById(int $id)
   {
      Novel::with([
         'translator',
         'categories',
         'volumes.chapters'
     ])
     ->findOrFail($id)
     ->makeHidden(['updated_at']);
   }

   public function getNovelById(int|String $id)
   {
      return Novel::findOrFail($id);
   }
}
