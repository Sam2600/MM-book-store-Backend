<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Novel;
use App\Models\Volume;
use App\Models\Chapter;
use App\Models\UserChapter;
use App\Models\CoinHistory;
use App\Models\AuthorEarning;
use App\Helpers\Helper;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ChapterRegisterRequest;
use App\Interfaces\Novel\NovelRepositoryInterface;
use App\Interfaces\Volume\VolumeRepositoryInterface;
use App\Interfaces\Chapter\ChapterRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Smalot\PdfParser\Parser;
use Illuminate\Support\HtmlString;
use Illuminate\Http\Request;
use App\Models\NovelView;
use App\Models\ChapterRead;

class ChapterController extends Controller
{
    use Helper, ApiResponse;

    public function __construct(
        private NovelRepositoryInterface $novelI,
        private VolumeRepositoryInterface $volumeI,
        private ChapterRepositoryInterface $chapterI,
    ) {}

    public function store(ChapterRegisterRequest $request): JsonResponse
    {
        /** @var \Illuminate\Http\Request $request */

        $relativePath = "";

        try {

            DB::beginTransaction();

            // 0. VERIFY NOVEL OWNERSHIP
            $novel = Novel::where('id', $request->novel_id)
                ->where('translator_id', $request->user()->id)
                ->first();

            if (!$novel) {
                return $this->forBidden('You do not have permission to add chapters to this novel.');
            }

            // 1. VALIDATE VOLUME SEQUENCE
            $maxVolume = Volume::where('novel_id', $request->novel_id)->max('volume_number') ?? 0;
            
            // If the requested volume is more than 1 step ahead of the current max
            if ($request->volume_number > ($maxVolume + 1)) {
                return $this->error("You cannot skip volumes. The next available volume is " . ($maxVolume + 1));
            }

            $volume = $this->volumeI->checkVolumeByIds($request->novel_id, $request->volume_number);

            if (is_null($volume)) {

                $vol = [
                    "novel_id" => $request->novel_id,
                    "volume_number" => $request->volume_number,
                    "volume_title" => $request->volume_title ?? "",
                ];

                $volume = Volume::create($vol);
            } else {
                $volume->update(['volume_title' => $request->volume_title ?? $volume->volume_title]);
            }

            // 2. VALIDATE CHAPTER SEQUENCE
            // Check the max chapter specifically within this novel
            $maxChapter = Chapter::whereHas('volume', function($q) use ($request) {
                $q->where('novel_id', $request->novel_id);
            })->max('chapter_number') ?? 0;

            if ($request->chapter_number > ($maxChapter + 1)) {
                return $this->error("You cannot skip chapters. The next available chapter is " . ($maxChapter + 1));
            }

            // 3. PREVENT DUPLICATE CHAPTERS
            $exists = Chapter::where('volume_id', $volume->id)
                ->where('chapter_number', $request->chapter_number)
                ->exists();
            
            if ($exists) {
                return $this->error("Chapter {$request->chapter_number} already exists in this volume.");
            }

            $path = "/novels/" . $this->sanitizeDirName($request->novel_name);

            $this->checkAndCreateDirectory($this->getDefaultDisk(), $path);

            $uniqueFileName = $this->generateChapterFileName($request->chapter_number, $request->title, "pdf");

            $content = $request->content;

            $relativePath = "{$path}/{$uniqueFileName}";

            $fullStoragePath = public_path("storage/{$relativePath}");

            Pdf::loadView("pdf.chapter", compact("content"))->save($fullStoragePath);

            $chpt = [
                "volume_id"      => $volume->id,
                "chapter_number" => $request->chapter_number,
                "title"          => $request->title,
                "content"        => $request->content,
                "coin_cost"      => $request->coin_cost ?? 1,
                "status"         => "approved", // Never accept status from client
                "file_path"      => $relativePath,
            ];

            $chapter = Chapter::create($chpt);

            DB::commit();

            $data = compact("volume", "chapter");

            return $this->success( __("messages.SS001", ["attribute" => "Chapter"]), $data);

        } catch (\Throwable $th) {

            DB::rollBack();

            // If a file was successfully uploaded before the error, delete it.
            $this->deleteFile($this->getDefaultDisk(), $relativePath);
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function show(int|String $novelId, int|String $volumeId, int|String $chapterId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $novel = $this->novelI->getNovelById($novelId);
            if (is_null($novel)) {
                return $this->error(__("messages.SE004", ["attribute" => "Novel"]));
            }

            $chapter = $this->findChapter($novel, $volumeId, $chapterId);
            if (is_null($chapter)) {
                return $this->error(__("messages.SE004", ["attribute" => "Chapter"]));
            }
            
            $this->recordChapterRead($novel, $chapter);

            $this->attachPagination($novel, $chapter);

            DB::commit();

            return $this->success( __("messages.SS008"), $chapter);

        } catch (\Throwable $th) {

            DB::rollBack();

            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    /**
     * TODO later this func will be used
     */
    public function showWithChapterAuthFeature(int|String $novelId, int|String $volumeId, int|String $chapterId): JsonResponse
    {
        
        try {

            $user_id = request()->query("user_id", null);

            // Check if the novel exists
            $novel = $this->novelI->getNovelById($novelId);

            if (is_null($novel)) {
                return $this->error(__("messages.SE004", ["attribute" => "Novel"]));
            }

            if ($volumeId > 2 && $user_id === null) {
                return $this->error(__("messages.SE018", ["attribute" => "Chapter"]));
            }

            $userChapterIds = [];

            if ($volumeId > 2 && $user_id !== null) {
                $userChapterIds = $this->novelI->getCurrentUserNovelChapters($novelId, $user_id);
            }

            $chapter = $novel->chapters()
                ->whereHas('volume', function ($q) use ($volumeId) {
                    $q->where('volume_number', $volumeId);
                })
                ->where('chapter_number', $chapterId)
                ->first();

            if (is_null($chapter)) {
                return $this->error(__("messages.SE004", ["attribute" => "Chapter"]));
            }

            if (count($userChapterIds) && !$userChapterIds->contains($chapter->id)) {
                return $this->error(__("messages.SE018", ["attribute" => "Chapter"]));
            }

            $chapter_numbers = $novel->chapters->pluck('chapter_number')->toArray();

            $next_chapter = null;
            $prev_chapter = null;

            if(count($chapter_numbers) > 0) {
                $next_chapter = in_array($chapter->chapter_number + 1, $chapter_numbers) ? $chapter->chapter_number + 1 : null;
                $prev_chapter = in_array($chapter->chapter_number - 1, $chapter_numbers) ? $chapter->chapter_number - 1 : null;
            }

            $chapter->next_chapter = $next_chapter;
            $chapter->prev_chapter = $prev_chapter;

            return $this->success( __("messages.SS008"), $chapter);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function getChapterEditData(int|String $chapterId): JsonResponse
    {
        try {
            $chapter = $this->chapterI->getChapterDetailsById($chapterId);

            if (is_null($chapter)) {
                return $this->error(__("messages.SE004", ["attribute" => "Chapter"]));
            }

            return $this->success( __("messages.SS008"), $chapter);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function getChapterDetails(int|String $chapterId): JsonResponse
    {
        try {
            $chapter = $this->chapterI->getChapterDetailsById($chapterId);

            if (is_null($chapter)) {
                return $this->error(__("messages.SE004", ["attribute" => "Chapter"]));
            }

            return $this->success( __("messages.SS008"), $chapter);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    /**
     * Handle the purchase of a chapter by a user, ensuring transactional integrity and proper error handling.
     * @param Request $request
     * @param mixed $id
     */
    public function purchaseChapter(Request $request, $novelId, $volumeId, $chapterId)
    {
        DB::beginTransaction();

        try {

            // Lock the user row for the duration of the transaction to prevent
            // a race condition where two concurrent requests both pass the coin
            // balance check before either decrement has committed.
            $user = User::where('id', $request->user()->id)->lockForUpdate()->firstOrFail();

            // Check if the novel exists
            $novel = $this->novelI->getNovelById($novelId);

            if (is_null($novel)) {
                return $this->error(__("messages.SE004", ["attribute" => "Novel"]));
            }

            $chapter = $novel->chapters()
                ->whereHas('volume', function ($q) use ($volumeId) {
                    $q->where('volume_number', $volumeId);
                })
                ->where('chapter_number', $chapterId)
                ->firstOrFail();

            if (is_null($chapter)) {
                return $this->error(__("messages.SE004", ["attribute" => "Chapter"]));
            }
            
            if ($this->chapterI->isChapterUnlocked($user->id, $chapter->id)) {
                return $this->error(__("messages.SE019"), []);
            }

            if ($user->coins < $chapter->coin_cost) {
                return $this->error(__("messages.SE020"), []);
            }

            $user_chapter = [
                "user_id" => $user->id,
                "novel_id" => $novel->id,
                "volume_id" => $chapter->volume_id,
                "chapter_id" => (int) $chapter->id,
            ];

            UserChapter::create($user_chapter);

            $coin_hist = [
                "user_id" => $user->id,
                "novel_id" => $novel->id,
                "volume_id" => $chapter->volume_id,
                "chapter_id" => (int) $chapter->id,
                "status" => "spent",
                "coin_amount" => $chapter->coin_cost,
                "description" => "Purchased chapter: {$chapter->title}",
            ];
            
            CoinHistory::create($coin_hist);

            $author_earn = [
                "translator_id" => $chapter->volume->novel->translator_id,
                "chapter_id" => (int) $chapter->id,
                "coins_earned" => $chapter->coin_cost,
                "earned_at" => now()
            ];

            AuthorEarning::create($author_earn);

            // Decrement user's coins after successful purchase
            User::where('id', $user->id)->decrement('coins', $chapter->coin_cost);

            DB::commit();

            return $this->success(__("messages.SS010", ["attribute" => "Chapter"]), []);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    public function updateChapter(ChapterRegisterRequest $request, int|String $id): JsonResponse
    {
        /** @var \Illuminate\Http\Request $request */

        $relativePath = "";

        try {

            DB::beginTransaction();

            $chapter = Chapter::findOrFail($id);

            // 0. VERIFY NOVEL OWNERSHIP
            $novel = Novel::where('id', $request->novel_id)
                ->where('translator_id', $request->user()->id)
                ->first();

            if (!$novel) {
                return $this->forBidden('You do not have permission to edit chapters of this novel.');
            }

            // 1. VALIDATE VOLUME SEQUENCE
            $maxVolume = Volume::where('novel_id', $request->novel_id)->max('volume_number') ?? 0;

            if ($request->volume_number > ($maxVolume + 1)) {
                return $this->error("You cannot skip volumes. The next available volume is " . ($maxVolume + 1));
            }

            // Resolve or create the target volume (same logic as store)
            $volume = $this->volumeI->checkVolumeByIds($request->novel_id, $request->volume_number);

            if (is_null($volume)) {
                $volume = Volume::create([
                    "novel_id"      => $request->novel_id,
                    "volume_number" => $request->volume_number,
                    "volume_title"  => $request->volume_title ?? "",
                ]);
            } else {
                $volume->update(['volume_title' => $request->volume_title ?? $volume->volume_title]);
            }

            // 2. VALIDATE CHAPTER SEQUENCE (exclude current chapter so it doesn't skew the max)
            $maxChapter = Chapter::whereHas('volume', function ($q) use ($request) {
                $q->where('novel_id', $request->novel_id);
            })->where('id', '!=', $id)->max('chapter_number') ?? 0;

            if ($request->chapter_number > ($maxChapter + 1)) {
                return $this->error("You cannot skip chapters. The next available chapter is " . ($maxChapter + 1));
            }

            // 3. PREVENT DUPLICATE CHAPTERS in target volume (exclude current chapter)
            $exists = Chapter::where('volume_id', $volume->id)
                ->where('chapter_number', $request->chapter_number)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->error("Chapter {$request->chapter_number} already exists in this volume.");
            }

            $path = "/novels/" . $this->sanitizeDirName($request->novel_name);

            $this->checkAndCreateDirectory($this->getDefaultDisk(), $path);

            $uniqueFileName = $this->generateChapterFileName($request->chapter_number, $request->title, "pdf");

            $content = $request->content;

            $relativePath = "{$path}/{$uniqueFileName}";

            $fullStoragePath = public_path("storage/{$relativePath}");

            Pdf::loadView("pdf.chapter", compact("content"))->save($fullStoragePath);

            // Delete old chapter file if it exists
            if ($chapter->file_path) {
                $this->deleteFile($this->getDefaultDisk(), $chapter->file_path);
            }

            $chpt = [
                "volume_id"      => $volume->id,
                "chapter_number" => $request->chapter_number,
                "title"          => $request->title,
                "content"        => $request->content,
                "coin_cost"      => $request->coin_cost ?? 1,
                "status"         => "approved", // Never accept status from client
                "file_path"      => $relativePath,
            ];

            $chapter->update($chpt);

            DB::commit();

            return $this->success( __("messages.SS002", ["attribute" => "Chapter"]), compact("chapter"));

        } catch (\Throwable $th) {

            DB::rollBack();

            // If a new file was successfully uploaded before the error, delete it.
            if (isset($relativePath) && !empty($relativePath)) {
                $this->deleteFile($this->getDefaultDisk(), $relativePath);
            }
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    private function sanitizeDirName(string $name): string
    {
        // Remove characters invalid in Windows directory names: < > : " / \ | ? *
        $name = preg_replace('/[<>:"\/\\\\|?*]/', '', $name);
        // Strip trailing periods and spaces — Windows rejects directory names ending with these
        return rtrim($name, '. ');
    }

    private function generateChapterFileName(int|string $id, string $title, string $extension): string
    {
        return "chapter-{$id}_({$title})_" . now()->format("Ymd_His") . "_" . Str::random(8) . ".{$extension}";
    }

    /**
     * Records a chapter read with 24-hour deduplication per user/IP.
     * This is the metric used for author payout calculations.
     */
    private function recordChapterRead($novel, $chapter): void
    {
        $ip     = request()->ip();
        $userId = request()->query("user_id");

        $recentReadExists = ChapterRead::where('chapter_id', $chapter->id)
            ->where(function ($query) use ($userId, $ip) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('ip_address', $ip);
                }
            })
            ->where('created_at', '>', now()->subDay())
            ->exists();

        if (!$recentReadExists) {
            ChapterRead::create([
                'chapter_id' => $chapter->id,
                'novel_id'   => $novel->id,
                'user_id'    => $userId,
                'ip_address' => $ip,
            ]);
        }
    }

    /**
     * Helper to find the specific chapter within a novel/volume.
     */
    private function findChapter($novel, $volumeNumber, $chapterNumber)
    {
        return $novel->chapters()
            ->with('volume')
            ->whereHas('volume', fn($q) => $q->where('volume_number', $volumeNumber))
            ->where('chapter_number', $chapterNumber)
            ->first();
    }

    /**
     * Attach next/prev navigation to the chapter.
     *
     * Chapters are ordered by (volume_number ASC, chapter_number ASC) across
     * the whole novel. Each result carries the volume_number so the frontend
     * can build the correct URL even when crossing volume boundaries.
     *
     * Returns objects of the form { volume_number, chapter_number } or null.
     */
    private function attachPagination($novel, &$chapter): void
    {
        // Flat ordered list of every chapter in the novel.
        // Query from Chapter directly (not through the hasManyThrough relationship)
        // to avoid a duplicate 'volumes' alias — the relationship already adds that join.
        $ordered = Chapter::join('volumes', 'chapters.volume_id', '=', 'volumes.id')
            ->where('volumes.novel_id', $novel->id)
            ->whereNull('chapters.deleted_at')
            ->whereNull('volumes.deleted_at')
            ->orderBy('volumes.volume_number')
            ->orderBy('chapters.chapter_number')
            ->select('chapters.chapter_number', 'volumes.volume_number')
            ->get();

        // Locate the current chapter in that ordered list
        $currentIndex = $ordered->search(function ($row) use ($chapter) {
            return (int) $row->volume_number === (int) $chapter->volume->volume_number
                && (int) $row->chapter_number === (int) $chapter->chapter_number;
        });

        $prev = ($currentIndex !== false && $currentIndex > 0)
            ? $ordered[$currentIndex - 1]
            : null;

        $next = ($currentIndex !== false && $currentIndex < $ordered->count() - 1)
            ? $ordered[$currentIndex + 1]
            : null;

        $chapter->prev_chapter = $prev
            ? ['volume_number' => (int) $prev->volume_number, 'chapter_number' => (int) $prev->chapter_number]
            : null;

        $chapter->next_chapter = $next
            ? ['volume_number' => (int) $next->volume_number, 'chapter_number' => (int) $next->chapter_number]
            : null;
    }
}
