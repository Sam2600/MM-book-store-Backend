<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Rating;
use App\Helpers\Helper;
use App\Models\Bookmark;
use App\Models\NovelView;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\NovelRegisterRequest;
use App\Interfaces\Novel\NovelRepositoryInterface;
use App\Interfaces\Rating\RatingRepositoryInterface;
use App\Interfaces\Category\CategoryRepositoryInterface;
use App\Interfaces\Bookmark\BookmarkRepositoryInterface;

class NovelController extends Controller
{
    use Helper, ApiResponse;

    public function __construct(
        private NovelRepositoryInterface $novelI,
        private CategoryRepositoryInterface $categoryI,
        private BookmarkRepositoryInterface $bookmarkI,
        private RatingRepositoryInterface $ratingI,
    ){}
    
    public function index(): JsonResponse
    {
        try {

            $all_novel = $this->novelI->getNovels();
            $categories = $this->categoryI->getCategories();
            $latest_novel = $this->novelI->getLatestNovels();
            $popular_week = $this->novelI->getPopularThisWeekNovels();
            $popular_month = $this->novelI->getPopularThisMonthNovels();
            $popular_all_time = $this->novelI->getPopularAllTimeNovels();

            $disk = $this->getDisk();

            /** @var Illuminate\Support\Facades\Storage $storage */
            $storage = Storage::disk($disk);

            $mapCoverImage = function(Novel $novel) use ($storage) {
                
                $novel->cover_image = !empty($novel->cover_image)
                    ? $storage->url($novel->cover_image)
                    : $storage->url(config("default.image.cover"));
    
                return $novel;
            };

            $latest_novel = $latest_novel->map($mapCoverImage);
            $popular_week = $popular_week->map($mapCoverImage);
            $popular_month = $popular_month->map($mapCoverImage);
            $popular_all_time = $popular_all_time->map($mapCoverImage);

            $data = compact(
                "all_novel",
                "categories",
                "latest_novel",
                "popular_week",
                "popular_month",
                "popular_all_time"
            );

            return $this->success(
                __("messages.SS008"), $data
            );

        } catch (\Throwable $th) {
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function store(NovelRegisterRequest $request): JsonResponse
    {   
        /** @var \Illuminate\Http\Request $request */

        $path = "";

        try {

            DB::beginTransaction();

            if ($request->hasFile("cover_image")) {
                $path = $this->storeFile($request);
            }

            $novel = [
                "cover_image" => $path,
                "title" => $request->title,
                "description" => $request->description,
                "translator_id" => $request->user()->id,
                "original_book_name" => $request->original_book_name,
                "original_author_name" => $request->original_author_name,
                "status" => $request->status == 1 ? "completed" : "ongoing",
            ];

            $novel = Novel::create($novel);

            $novel->categories()->attach($request->categories);

            DB::commit();

            return $this->success( __("messages.SS001", ["attribute" => "Novel"]));

        } catch (\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            if (!empty($path)) {
                $disk = $this->getDisk();
                $this->deleteFile($disk, $path);
            }

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function show(int|String $id): JsonResponse
    {
        try {

            $user_id = request()->query("user_id");
            
            DB::beginTransaction();

            $novel = $this->novelI->getNovelDetailInfoById($id, $user_id);

            if (empty($novel)) {
                return $this->error(__("messages.SE004"), ["attribute" => "Novel"]);
            }
            
            $disk = $this->getDisk();

            /** @var Illuminate\Support\Facades\Storage $storage */
            $storage = Storage::disk($disk);

            $img_url = !empty($novel->cover_image)
                ? $storage->url($novel->cover_image)
                : $storage->url(config("default.image.cover"));

            $novel->cover_image = $img_url;

            $data = [
                "novel_id" => $novel->id,
                "user_id" => null,
                "ip_address" => request()->ip(),
            ];

            NovelView::create($data);

            $novel->increment("view_count");

            DB::commit();

            return $this->success(__("messages.SS008"), $novel);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function getNovelsByAuthor(): JsonResponse
    {
        $novels = $this->novelI->getNovelsByAuthor();

        return $this->success(__("messages.SS008"), $novels);
    }

    public function bookmarkNovel(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();

            $user = Auth::user();

            $novel = $this->novelI->getNovelDetailInfoById($request->novel_id, $user->id);

            if (empty($novel)) {
                return $this->error(__("messages.SE004"), ["attribute" => "Novel"]);
            }

            $isBookmarkExists = $this->bookmarkI->checkBookmarkExists($request->novel_id, $user->id);

            if ($isBookmarkExists) {

                Bookmark::where('user_id', $user->id)
                    ->where('novel_id', $request->novel_id)
                    ->onlyTrashed()
                    ->restore();

            } else {

                $data = [
                    "user_id" => $user->id,
                    "novel_id" => $request->novel_id,
                ];

                Bookmark::create($data);
            }

            DB::commit();

            return $this->success(__("messages.SS001", ["attribute" => "Bookmark"]), []);

        } catch (\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function removeBookmarkNovel(int $id): JsonResponse
    {
        try {

            DB::beginTransaction();

            $user = Auth::user();
            
            $bookmark = Bookmark::where('user_id', $user->id)->where('novel_id', $id)->first();

            if (empty($bookmark)) {
                return $this->error(__("messages.SE004", ["attribute" => "Bookmark"]));
            }

            $bookmark->delete();

            DB::commit();

            return $this->success(__("messages.SS003", ["attribute" => "Bookmark"]), []);

        } catch (\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function getBookMarkedCollection(): JsonResponse
    {
        try {

            DB::beginTransaction();

            $user = Auth::user();

            $novel_ids = $this->novelI->getBookMarks($user->id);

            if(count($novel_ids) == 0) {
                return $this->notFound(__("messages.SE004", ["attribute" => "Bookmarks"]));
            }

            $novels = $this->novelI->getNovelByBookMarks($novel_ids);

            $disk = $this->getDisk();

            /** @var Illuminate\Support\Facades\Storage $storage */
            $storage = Storage::disk($disk);

            $mapCoverImage = function(Novel $novel) use ($storage): Novel {
                
                $novel->cover_image = !empty($novel->cover_image)
                    ? $storage->url($novel->cover_image)
                    : $storage->url(config("default.image.cover"));
    
                return $novel;
            };

            $novels = $novels->map($mapCoverImage);

            return $this->success(__("messages.SS008"), $novels);

        } catch (\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function rateNovel(Request $request, int|string $id): JsonResponse
    {
        try {

            DB::beginTransaction();

            $user = Auth::user();

            $novel = $this->novelI->getNovelDetailInfoById($id, $user->id);

            if (empty($novel)) {
                return $this->error(__("messages.SE004"), ["attribute" => "Novel"]);
            }

            $isRatingExists = $this->ratingI->checkRatingExists($id, $user->id);

            if ($isRatingExists) {

                Rating::where('user_id', $user->id)
                    ->where('novel_id', $id)
                    ->update(["rating" => $request->rating]);

            } else {

                $data = [
                    "user_id" => $user->id,
                    "novel_id" => $id,
                    "rating" => $request->rating
                ];

                Rating::create($data);
            }

            DB::commit();

            $stats = Rating::where('novel_id', $id)
                ->selectRaw('AVG(rating) as average, COUNT(*) as total')
                ->first();

            return $this->success(__("messages.SS001", ["attribute" => "Rating"]), $stats);


        } catch (\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function getNovelsByCategory($id) {

        try {

            $novels = Novel::select(['novels.id', 'novels.title', 'novels.status', 'novels.cover_image'])
            ->withCount('views AS total_view_cnt')
            ->whereHas('categories', function ($query) use ($id) {
                $query->where('categories.id', $id);
            })
            ->paginate(15);

            $disk = $this->getDisk();
            $storage = Storage::disk($disk);

            $novels->getCollection()->transform(function(Novel $novel) use ($storage) {
                $novel->cover_image = !empty($novel->cover_image)
                    ? $storage->url($novel->cover_image)
                    : $storage->url(config("default.image.cover"));
                return $novel;
            });

            return $this->success(__("messages.SS008"), $novels);

        } catch (\Throwable $th) {
            
            $this->logException($th);
            return $this->error(__("messages.SE010"), []);
        }
    }

    private function storeFile(NovelRegisterRequest $request): String
    {   
         /** @var \Illuminate\Http\Request $request */

        $file = $request->file("cover_image");

        $filename = uniqid()."_".$file->getClientOriginalName();

        return $file->storeAs("uploads", $filename, "public");
    }

    private function deleteFile(String $disk, String $path): void
    {
        Storage::disk($disk)->delete($path);
    }

    private function getDisk(): String
    {
        return config("filesystems.default");
    }
}
