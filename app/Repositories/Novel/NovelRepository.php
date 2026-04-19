<?php

namespace App\Repositories\Novel;

use Carbon\Carbon;
use App\Models\Novel;
use App\Models\Chapter;
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

   public function getPopularThisWeekNovels(int $limit = 12)
   {
      $weekStart = Carbon::now()->subWeek()->startOfDay();
      
      return Novel::query()
         ->select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
         ->withCount('views AS total_view_cnt')
         ->selectRaw('COUNT(novel_views.id) AS monthly_views')
         ->selectRaw('(SELECT ROUND(AVG(r.rating), 1) FROM ratings r WHERE r.novel_id = novels.id AND r.deleted_at IS NULL) AS average_rating')
         ->with('categories:id,name')
         ->join('novel_views', 'novels.id', '=', 'novel_views.novel_id')
         ->whereNull('novel_views.deleted_at')
         ->where('novel_views.created_at', '>=', $weekStart)
         ->groupBy('novels.id')
         ->orderByRaw('COUNT(novel_views.novel_id) DESC')
         ->limit($limit)
         ->get();
   }

   public function getPopularAllTimeNovels(int $limit = 6)
   {
      return Novel::with('categories')->select('id', 'title', 'description', 'cover_image', 'status')->selectRaw('view_count AS total_view_cnt')->orderByDesc('view_count')->take($limit)->get()->makeHidden(['created_at', 'updated_at']);
   }

   public function getPopularThisMonthNovels(int $limit = 12)
   {
      $monthStart = now()->startOfMonth();
      
      return Novel::query()
         ->select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
         ->withCount('views AS total_view_cnt')
         ->selectRaw('COUNT(novel_views.id) AS monthly_views')
         ->selectRaw('(SELECT ROUND(AVG(r.rating), 1) FROM ratings r WHERE r.novel_id = novels.id AND r.deleted_at IS NULL) AS average_rating')
         ->with('categories:id,name')
         ->join('novel_views', 'novels.id', '=', 'novel_views.novel_id')
         ->whereNull('novel_views.deleted_at')
         ->whereBetween('novel_views.created_at', [$monthStart, now()])
         ->groupBy('novels.id')
         ->orderByRaw('COUNT(novel_views.novel_id) DESC')
         ->limit($limit)
         ->get();
   }

   public function getPopularWeekPaginated(int $perPage = 15)
   {
      $weekStart = Carbon::now()->subWeek()->startOfDay();

      return Novel::query()
         ->select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
         ->withCount('views AS total_view_cnt')
         ->selectRaw('COUNT(novel_views.id) AS weekly_views')
         ->selectRaw('(SELECT ROUND(AVG(r.rating), 1) FROM ratings r WHERE r.novel_id = novels.id AND r.deleted_at IS NULL) AS average_rating')
         ->with('categories:id,name')
         ->join('novel_views', 'novels.id', '=', 'novel_views.novel_id')
         ->whereNull('novel_views.deleted_at')
         ->where('novel_views.created_at', '>=', $weekStart)
         ->groupBy('novels.id')
         ->orderByRaw('COUNT(novel_views.novel_id) DESC')
         ->paginate($perPage);
   }

   public function getPopularMonthPaginated(int $perPage = 15)
   {
      $monthStart = now()->startOfMonth();

      return Novel::query()
         ->select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
         ->withCount('views AS total_view_cnt')
         ->selectRaw('COUNT(novel_views.id) AS monthly_views')
         ->selectRaw('(SELECT ROUND(AVG(r.rating), 1) FROM ratings r WHERE r.novel_id = novels.id AND r.deleted_at IS NULL) AS average_rating')
         ->with('categories:id,name')
         ->join('novel_views', 'novels.id', '=', 'novel_views.novel_id')
         ->whereNull('novel_views.deleted_at')
         ->whereBetween('novel_views.created_at', [$monthStart, now()])
         ->groupBy('novels.id')
         ->orderByRaw('COUNT(novel_views.novel_id) DESC')
         ->paginate($perPage);
   }

   public function getLatestNovels(int $limit = 6)
   {
      return Novel::with(['translator' => function($query) {
            $query->select('id', 'name');
         }])->select('id', 'translator_id', 'title', 'cover_image', 'view_count', 'created_at')
         ->orderBy('created_at', 'desc')
         ->take($limit)
         ->get();
   }

   public function getNovelsByAuthor()
   {
      // Get currently authenticated user
      $user = Auth::user();

      // Get novels where user is translator
      return Novel::where('translator_id', $user->id)->select('id', 'title')->get();
   }

   public function getNovelDetailInfoById(int|string $id, int|string|null $user_id)
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
      ->withCount('ratings as user_rating_count')
      ->withAvg('ratings as average_rating', 'rating')
      ->find($id);
   }

   public function getNovelById(int|string $id)
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

   public function getLatestUpdatedNovels(int $limit = 12)
   {
      $novels = Novel::query()
         ->select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
         ->join('volumes', 'novels.id', '=', 'volumes.novel_id')
         ->join('chapters', 'volumes.id', '=', 'chapters.volume_id')
         ->whereNull('chapters.deleted_at')
         ->whereNull('volumes.deleted_at')
         ->groupBy('novels.id', 'novels.title', 'novels.status', 'novels.cover_image')
         ->orderByRaw('MAX(chapters.created_at) DESC')
         ->limit($limit)
         ->get();

      foreach ($novels as $novel) {
         $novel->latest_chapters = Chapter::query()
            ->join('volumes', 'chapters.volume_id', '=', 'volumes.id')
            ->where('volumes.novel_id', $novel->id)
            ->whereNull('chapters.deleted_at')
            ->whereNull('volumes.deleted_at')
            ->select([
               'chapters.id',
               'chapters.chapter_number',
               'chapters.title',
               'chapters.created_at',
               'volumes.id as volume_id',
               'volumes.volume_number',
            ])
            ->orderByDesc('chapters.created_at')
            ->limit(3)
            ->get();
      }

      return $novels;
   }

   public function getEndedNovels(int $limit = 12)
   {
      return Novel::where('status', 'completed')
         ->select('id', 'title', 'cover_image', 'status', 'updated_at')
         ->selectRaw('(SELECT ROUND(AVG(r.rating), 1) FROM ratings r WHERE r.novel_id = novels.id AND r.deleted_at IS NULL) AS average_rating')
         ->orderByDesc('updated_at')
         ->limit($limit)
         ->get();
   }

   public function getAllEndedNovels()
   {
      return Novel::where('status', 'completed')
         ->select('id', 'title', 'cover_image', 'status', 'updated_at')
         ->orderByDesc('updated_at')
         ->get();
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
