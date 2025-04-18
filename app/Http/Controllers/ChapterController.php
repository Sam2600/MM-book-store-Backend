<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Volume;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChapterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'novel_id' => 'required|exists:novels,id',
            'volume_id' => 'required|integer',
            'chapter_id' => 'required|integer',
            'title' => 'required|string',
            'volume_title' => 'required|string',
            'content' => 'required|string',
        ]);

        try {

            DB::beginTransaction();

            $volume = Volume::where('volume_number', $validated['volume_id'])
                        ->where('novel_id', $validated['novel_id'])
                        ->first();

            if (!$volume) {

                // Create a new volume if none provided
                $volumeCount = Volume::where('volume_number', $validated['novel_id'])->count();

                $volume = Volume::create([
                    'volume_number' => $validated['volume_id'],
                    'novel_id' => $validated['novel_id'],
                    'volume_title' => $validated['volume_title'] ?? 'Volume ' . ($volumeCount + 1),
                    'order' => $volumeCount + 1,
                ]);
            }

            $chapter = Chapter::create([
                'volume_id' => $volume->volume_number,
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Chapter is created successfully.',
                'data' => [
                    'chapter' => $chapter,
                    'volume' => $volume,
                ]
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();
            
            Log::info($e->getMessage());

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function show($novelId, $volumeId, $chapterId)
    {
        
        try {
            // Check if the novel exists
            $novel = Novel::findOrFail($novelId);

            // Find the chapter belonging to this novel
            $volume = Volume::where('novel_id', $novelId)
                            ->where('volume_number', $volumeId)
                            ->firstOrFail();

            // Find the chapter belonging to this novel
            $chapter = Chapter::where('volume_id', $volume->volume_number)
                            ->where('id', $chapterId)
                            ->firstOrFail();

        } catch (\Exception $e) {

            DB::rollBack();
            
            Log::info($e->getMessage());

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'novel' => $novel,
            'chapter' => $chapter
        ]);
    }
}
