<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Volume;
use App\Models\Chapter;
use App\Helpers\Helper;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ChapterRegisterRequest;
use App\Interfaces\Novel\NovelRepositoryInterface;
use App\Interfaces\Volume\VolumeRepositoryInterface;
use App\Interfaces\Chapter\ChapterRepositoryInterface;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Smalot\PdfParser\Parser;
use Illuminate\Support\HtmlString;

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

            $volume = $this->volumeI->checkVolumeByIds($request->novel_id, $request->volume_number);

            if (is_null($volume)) {

                // Create a new volume if none provided
                $volumeCount = $this->volumeI->getNovelTotalVolumeById($request->novel_id);

                $vol = [
                    "order" => $volumeCount + 1,
                    "novel_id" => $request->novel_id,
                    "volume_number" => $request->volume_number,
                    "volume_title" => $request->volume_title ?? "",
                ];

                $volume = Volume::create($vol);
            }

            $path = "/novels/{$request->novel_name}";

            $this->checkAndCreateDirectory($this->getDefaultDisk(), $path);

            $uniqueFileName = $this->generateChapterFileName($request->chapter_number, $request->title, "pdf");

            $content = $request->content;

            $relativePath = "{$path}/{$uniqueFileName}";

            $fullStoragePath = public_path("storage/{$relativePath}");

            Pdf::loadView("pdf.chapter", compact("content"))->save($fullStoragePath);

            $chpt = [
                "volume_id" => $volume->id,
                "chapter_number" => $request->chapter_number,
                "title" => $request->title,
                "content" => $request->content,
                "coin_cost" => $request->coin_cost ?? 1,
                "status" => $request->status ?? "approved",
                "file_path" => $relativePath
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
            // Check if the novel exists
            $novel = $this->novelI->getNovelById($novelId);

            // Find the chapter belonging to this novel
            $volume = $this->volumeI->checkVolumeByIds($novelId, $volumeId);
            // Find the chapter belonging to this novel
            $chapter = $this->chapterI->getChatperByIds($volume->id, $chapterId);

            $data = compact("novel", "chapter");

            return $this->success( __("messages.SS008"), $data);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }

    private function generateChapterFileName(int|string $id, string $title, string $extension): string
    {
        return "chapter-{$id}_({$title})_" . now()->format("Ymd_His") . "_" . Str::random(8) . ".{$extension}";
    }
}
