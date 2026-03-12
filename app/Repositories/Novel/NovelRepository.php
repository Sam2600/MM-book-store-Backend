<?php

namespace App\Repositories\Novel;

use Carbon\Carbon;
use App\Models\Novel;
use App\Helpers\Helper;
use App\Models\Category;
use App\Models\NovelView;
use App\Models\BookMark;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\Novel\NovelRepositoryInterface;
use Illuminate\Support\Facades\DB;

class NovelRepository implements NovelRepositoryInterface
{
   use Helper;

   public function getNovels()
   {
      return Novel::select('id', 'title')->get();
   }

   public function getPopularThisWeekNovels()
   {
      $weekStart = Carbon::now()->subWeek()->startOfDay();
      
      return Novel::query()
         ->select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
         ->selectRaw('COUNT(novel_views.id) as total_view_cnt')
         ->with('categories:id,name')
         ->join('novel_views', 'novels.id', '=', 'novel_views.novel_id')
         ->whereNull('novel_views.deleted_at') // Assuming soft deletes
         ->where('novel_views.created_at', '>=', $weekStart)
         ->groupBy('novels.id')
         ->orderByRaw('COUNT(novel_views.novel_id) DESC')
         ->limit(10)
         ->get();
   }

   public function getPopularAllTimeNovels()
   {
      return Novel::with('categories')->select('id', 'title', 'description', 'cover_image')->orderByDesc('view_count')->take(5)->get()->makeHidden(['created_at', 'updated_at']);
   }

   public function getPopularThisMonthNovels()
   {
      $monthStart = now()->startOfMonth();
      
      return Novel::query()
         ->select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
         ->selectRaw('COUNT(novel_views.id) as total_view_cnt')
         ->with('categories:id,name')
         ->join('novel_views', 'novels.id', '=', 'novel_views.novel_id')
         ->whereNull('novel_views.deleted_at')
         ->whereBetween('novel_views.created_at', [$monthStart, now()])
         ->groupBy('novels.id')
         ->orderByRaw('COUNT(novel_views.novel_id) DESC')
         ->limit(10)
         ->get();
   }

   public function getLatestNovels()
   {
      return Novel::with(['translator' => function($query) {
            $query->select('id', 'name');
         }])->select('id', 'translator_id', 'title', 'cover_image', 'view_count', 'created_at')
         ->orderBy('created_at', 'desc')
         ->take(5)
         ->get();
   }

   public function getNovelsByAuthor()
   {
      // Get currently authenticated user
      $user = Auth::user();

      // Get novels where user is translator
      return Novel::where('translator_id', $user->id)->select('id', 'title')->get();
   }

   public function getNovelDetailInfoById(int $id, int|string|null $user_id)
   {
      return Novel::with([
         'translator',
         'categories',
         'volumes.chapters',
         'bookmarks' => function ($query) use ($id, $user_id) {
            $query->where('novel_id', $id);
            $query->where('user_id', $user_id);
         },
      ])
      ->withCount([
         'ratings as user_rating_count' => function ($query) use ($id, $user_id) {
            $query->where('novel_id', $id);
               // ->where('user_id', $user_id);
         }
      ])
      // Add average rating column from all ratings
      ->withAvg('ratings as average_rating', 'rating')
      ->find($id)
      ->makeHidden(['updated_at']);
   }

   public function getNovelById(int|String $id)
   {
      return Novel::where('id', $id)->first();
   }

   public function getBookMarks(int|string $user_id)
   {
      return BookMark::where('user_id', $user_id)->pluck('novel_id')->toArray();
   }

   public function getNovelByBookMarks(array $novel_ids)
   {
      return Novel::whereIn('id', $novel_ids)->get();
   }

   public function getCurrentUserNovelChapters(int|string $novelId, int|string $userId)
   {
      return DB::table('user_chapters')
         ->where('user_id', '=', $userId)
         ->where('novel_id', '=', $novelId)
         ->select('chapter_id')
         ->pluck('chapter_id');
   }
}
