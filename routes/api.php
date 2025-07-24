<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\VolumeController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/novels', [NovelController::class, 'store']);
    Route::post('/novels/{novel}/volumes', [VolumeController::class, 'store']);
    Route::post('/chapters', [ChapterController::class, 'store']);
    Route::post('/novels/{novel}/categories', [CategoryController::class, 'assignCategories']);
    Route::post('/bookmarks', [NovelController::class, 'bookmarkNovel']);
    Route::patch('/novels/bookmarks/{id}', [NovelController::class, 'removeBookmarkNovel']);
    Route::get('/novelsByAuthors', [NovelController::class, 'getNovelsByAuthor']);
    Route::get('/getBookMarkedCollection', [NovelController::class, 'getBookMarkedCollection']);
});

Route::get('/novels', [NovelController::class, 'index']);
Route::get('/novels/{id}', [NovelController::class, 'show']);
Route::get('/novels/{novelId}/chapters/{chapterId}', [ChapterController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('chapter-image', function(){
    $disk = 'supabase.novel-coverimage';
    $heroImage = Storage::disk('public')->get('defaults/wizard.jpg');
    $uploadedPath = Storage::disk($disk)->put('wizard.jpg', $heroImage);
    return Storage::disk($disk)->url($uploadedPath);
});