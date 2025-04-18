<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Novel;
use App\Models\Category;
use App\Models\NovelView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelController extends Controller
{
    public function index()
    {

        try {

            $all_novel = $this->getAllNovels();
            $latest_novel = $this->latestNovels();
            $popular_week = $this->popularThisWeek();
            $popular_month = $this->popularThisMonth();
            $popular_all_time = $this->popularAllTime();
            $categories = $this->getAllCategories();

        } catch (\Exception $e) {
            
            Log::info($e->getMessage());

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            "all_novel" => $all_novel,
            "latest_novel" => $latest_novel, 
            "popular_week" => $popular_week, 
            "popular_month" => $popular_month, 
            "popular_all_time" => $popular_all_time,
            "categories" => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'original_author_name' => 'required|string',
            'original_book_name' => 'required|string',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|file|image|max:2048',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        try {

            DB::beginTransaction();

            $path = "";
            if ($request->hasFile('cover_image')) {
                $file = $request->file('cover_image');
                $filename = uniqid().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('uploads', $filename, 'public');
            }

            $novel = Novel::create([
                'translator_id' => $request->user()->id,
                'original_author_name' => $request->original_author_name,
                'original_book_name' => $request->original_book_name,
                'title' => $request->title,
                'description' => $request->description,
                'cover_image' => $path,
                'status' => $request->status == 1 ? "completed" : "ongoing",
            ]);

            $novel->categories()->attach($request->category_ids);

            DB::commit();

            return response()->json([
                'message' => 'Novel is created successfully'
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::info($e->getMessage());

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllNovels()
    {
        return Novel::select('id', 'title')->get();
    }

    public function popularThisWeek()
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $popular = Novel::with([
            'categories:id,name' // Eager load only id and name from categories
        ])->withCount([
            'views as view_count' => function ($query) use ($weekStart, $weekEnd) {
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

    public function popularAllTime()
    {
        return Novel::with('categories')->select('id', 'title', 'description', 'cover_image')->orderByDesc('view_count')->take(5)->get()->makeHidden(['created_at', 'updated_at']);
    }

    public function popularThisMonth()
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $popular = Novel::with([
            'categories:id,name' // Eager load only id and name from categories
        ])->withCount([
            'views as view_count' => function ($query) use ($monthStart, $monthEnd) {
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

    public function latestNovels() { 
        return Novel::select('id', 'title', 'description', 'created_at')->orderBy('created_at', 'desc')->take(5)->get();
    }

    public function getAllCategories() {
        return Category::select('id', 'name')->get();
    }

    public function show($id)
    {
        try {
            $novel = Novel::with([
                'translator',
                'categories',
                'volumes.chapters'
            ])
            ->findOrFail($id)
            ->makeHidden(['updated_at']);

            DB::beginTransaction();

            NovelView::create([
                'novel_id' => $novel->id,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);

            $novel->increment('view_count');

            DB::commit();

            return response()->json($novel);

        } catch (\Exception $e) {

            DB::rollBack();
            
            Log::info($e->getMessage());

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function assignCategories(Request $request, Novel $novel)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $novel->categories()->sync($request->category_ids);
        return response()->json(['message' => 'Categories assigned']);
    }

    public function getNovelsByAuthor(Request $user)
    {
        $user = Auth::user();

        $novels = Novel::where('translator_id', $user->id)->select('id', 'title')->get();

        return response()->json($novels);

    }
}
