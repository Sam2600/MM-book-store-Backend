<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\VolumeController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PaymentMethodController;
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
Route::post('/resend-verification', [UserController::class, 'resendVerification']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [UserController::class, 'getMyInfo']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/novels', [NovelController::class, 'store']);
    Route::post('/novels/{novel}/volumes', [VolumeController::class, 'store']);
    Route::post('/novels/{novel}/categories', [CategoryController::class, 'assignCategories']);
    Route::post('/novels/{novelId}/volumes/{volumeId}/chapters/{chapterId}/purchase', [ChapterController::class, 'purchaseChapter']);
    Route::post('/novels/{id}/rate', [NovelController::class, 'rateNovel']);
    Route::patch('/novels/bookmarks/{id}', [NovelController::class, 'removeBookmarkNovel']);
    Route::get('/novelsByAuthors', [NovelController::class, 'getNovelsByAuthor']);
    
    Route::get('/author', [NovelController::class, 'novelsByAuthor']);
    
    Route::post('/chapters', [ChapterController::class, 'store']);
    Route::get('/chapters/{id}', [ChapterController::class, 'getChapterDetails']);
    Route::put('/chapters/{id}', [ChapterController::class, 'updateChapter']);
    Route::get('/chapters/{id}/edit', [ChapterController::class, 'getChapterEditData']);
    
    Route::post('/bookmarks', [NovelController::class, 'bookmarkNovel']);
    Route::get('/getBookMarkedCollection', [NovelController::class, 'getBookMarkedCollection']);

    // Author: profile update, payment info + earnings + payouts
    Route::patch('/me/profile', [UserController::class, 'updateProfile']);
    Route::patch('/me/payment-info', [UserController::class, 'updatePaymentInfo']);
    Route::get('/author/earnings/stats', [EarningsController::class, 'authorStats']);
    Route::get('/author/payouts', [PayoutController::class, 'authorIndex']);

    // Admin: earnings calculation + payout management
    Route::post('/admin/earnings/calculate', [EarningsController::class, 'calculate']);
    Route::get('/admin/payouts', [PayoutController::class, 'adminIndex']);
    Route::post('/admin/payouts', [PayoutController::class, 'store']);
    Route::patch('/admin/payouts/{payout}/mark-paid', [PayoutController::class, 'markPaid']);
});

Route::get('/author/{id}', [UserController::class, 'getAuthorInfoAndNovels']);

Route::get('/novels', [NovelController::class, 'index']);
Route::get('/novels/ended', [NovelController::class, 'endedNovels']);
Route::get('/novels/search', [NovelController::class, 'search']);
Route::get('/novels/{id}', [NovelController::class, 'show']);
Route::get('/novels/{novelId}/volumes/{volumeId}/chapters/{chapterId}', [ChapterController::class, 'show']);

Route::get('/users/activate', [UserController::class, 'activate']);

Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}/novels', [NovelController::class, 'getNovelsByCategory']);