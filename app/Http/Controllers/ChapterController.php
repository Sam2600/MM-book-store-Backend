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

        try {

            DB::beginTransaction();

            $volume = $this->volumeI->checkVolumeByIds($request->novel_id, $request->volume_id);

            if (!$volume) {

                // Create a new volume if none provided
                $volumeCount = $this->volumeI->getNovelTotalVolumeById($request->novel_id);

                $vol = [
                    "order" => $volumeCount + 1,
                    "novel_id" => $request->novel_id,
                    "volume_number" => $request->volume_id,
                    "volume_title" => $request->volume_title ?? "Volume " . ($volumeCount + 1),
                ];

                $volume = Volume::create($vol);
            }

            $chpt = [
                "title" => $request->title,
                "content" => $request->content,
                "volume_id" => $volume->volume_number,
            ];

            $chapter = Chapter::create($chpt);

            DB::commit();

            $data = compact("volume", "chapter");

            return $this->success( __("messages.SS001", ["attribute" => "Chapter"]), $data);

        } catch (\Throwable $th) {

            DB::rollBack();
            
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
            $chapter = $this->chapterI->getChatperByIds($volume->volume_number, $chapterId);

            $data = compact("novel", "chapter");

            return $this->success( __("messages.SS008"), $data);

        } catch (\Throwable $th) {

            DB::rollBack();
            
            $this->logException($th);

            return $this->error(__("messages.SE010"), []);
        }
    }
}
