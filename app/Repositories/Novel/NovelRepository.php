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
      $weekStart = Carbon::now()->subDays(7)->toDateString();
      $weekEnd = Carbon::now()->toDateString();

      $ids = NovelView::whereBetween('created_at', [$weekStart, $weekEnd])
         ->where('deleted_at', null)
         ->selectRaw('count(novel_id) as view_count, novel_id')
         ->groupBy('novel_id')
         ->orderByDesc('view_count')
         ->take(10)
         ->pluck('novel_id');

      $popular = Novel::with([
         'categories:id,name'
      ])
      ->whereIn('id', $ids)
      ->select('id', 'title', 'status', 'cover_image')
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
      $monthStart = Carbon::now()->subDays(7)->toDateString();
      $monthEnd = Carbon::now()->toDateString();

      $ids = NovelView::whereBetween('created_at', [$monthStart, $monthEnd])
         ->where('deleted_at', null)
         ->selectRaw('count(novel_id) as view_count, novel_id')
         ->groupBy('novel_id')
         ->orderByDesc('view_count')
         ->take(10)
         ->pluck('novel_id');

      $popular = Novel::with([
         'categories:id,name'
      ])
      ->whereIn('id', $ids)
      ->select('id', 'title', 'status', 'cover_image')
      ->get()
      ->makeHidden(['created_at', 'updated_at']);

      return $popular;
   }

   public function getLatestNovels()
   {
      return Novel::select('id', 'title', 'description', 'cover_image', 'view_count', 'created_at')->orderBy('created_at', 'desc')->take(5)->get();
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
      return Novel::with([
         'translator',
         'categories',
         'volumes.chapters',
         'bookmarks' => function ($query) use ($id) {
            $query->where('novel_id', $id);
         }
      ])
      ->find($id)
      ->makeHidden(['updated_at']);
   }

   public function getNovelById(int|String $id)
   {
      return Novel::select('id')->where('id', $id)->first();
   }

   public function getBookMarks(int|string $user_id)
   {
      return BookMark::where('user_id', $user_id)->pluck('novel_id')->toArray();
   }

   public function getNovelByBookMarks(array $novel_ids)
   {
      return Novel::whereIn('id', $novel_ids)->get();
   }
}
