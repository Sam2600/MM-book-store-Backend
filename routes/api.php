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
*/

// ── Public routes ────────────────────────────────────────────────────────────

// Auth — stricter rate limit: 10 attempts per minute
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Email verification — 5 attempts per minute
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/resend-verification', [UserController::class, 'resendVerification']);
    Route::get('/users/activate',       [UserController::class, 'activate']);
});

Route::get('/author/{id}',              [UserController::class, 'getAuthorInfoAndNovels']);
Route::get('/novels',                   [NovelController::class, 'index']);
Route::get('/novels/ended',             [NovelController::class, 'endedNovels']);
Route::get('/novels/search',            [NovelController::class, 'search']);
Route::get('/novels/{id}',              [NovelController::class, 'show']);
Route::get('/novels/{novelId}/volumes/{volumeId}/chapters/{chapterId}', [ChapterController::class, 'show']);
Route::get('/payment-methods',          [PaymentMethodController::class, 'index']);
Route::get('/categories',               [CategoryController::class, 'index']);
Route::get('/categories/{id}/novels',   [NovelController::class, 'getNovelsByCategory']);


// ── Authenticated routes ──────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // General authenticated
    Route::get('/me',      [UserController::class, 'getMyInfo']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/novels/{id}/rate',          [NovelController::class, 'rateNovel']);
    Route::patch('/novels/bookmarks/{id}',    [NovelController::class, 'removeBookmarkNovel']);
    Route::post('/bookmarks',                 [NovelController::class, 'bookmarkNovel']);
    Route::get('/getBookMarkedCollection',    [NovelController::class, 'getBookMarkedCollection']);
    Route::get('/novelsByAuthors',            [NovelController::class, 'getNovelsByAuthor']);
    Route::get('/author',                     [NovelController::class, 'novelsByAuthor']);

    Route::get('/chapters/{id}',       [ChapterController::class, 'getChapterDetails']);
    Route::get('/chapters/{id}/edit',  [ChapterController::class, 'getChapterEditData']);

    Route::post('/novels/{novelId}/volumes/{volumeId}/chapters/{chapterId}/purchase',
        [ChapterController::class, 'purchaseChapter']);

    // Author: profile + earnings + payouts
    Route::patch('/me/profile',             [UserController::class, 'updateProfile']);
    Route::patch('/me/payment-info',        [UserController::class, 'updatePaymentInfo']);
    Route::get('/author/earnings/stats',    [EarningsController::class, 'authorStats']);
    Route::get('/author/payouts',           [PayoutController::class, 'authorIndex']);
    Route::post('/author/payouts/request',  [PayoutController::class, 'authorRequestPayout']);


    // ── Author-only write routes (role_id = 3 or 1) ──────────────────────────

    Route::middleware('author')->group(function () {
        Route::post('/novels',                                [NovelController::class, 'store']);
        Route::post('/novels/{novel}/volumes',                [VolumeController::class, 'store']);
        Route::post('/novels/{novel}/categories',            [CategoryController::class, 'assignCategories']);
        Route::post('/chapters',                             [ChapterController::class, 'store']);
        Route::put('/chapters/{id}',                         [ChapterController::class, 'updateChapter']);
    });


    // ── Admin-only routes (role_id = 1) ──────────────────────────────────────

    Route::middleware('admin')->group(function () {

        // Earnings
        Route::post('/admin/earnings/calculate',                    [EarningsController::class, 'calculate']);
        Route::post('/admin/earnings/calculate-and-create-payouts', [EarningsController::class, 'calculateAndCreatePayouts']);

        // Payouts — specific paths before wildcard {payout}
        Route::get('/admin/payouts/summary',        [PayoutController::class, 'summary']);
        Route::get('/admin/payouts/export',         [PayoutController::class, 'export']);
        Route::post('/admin/payouts/bulk-create',   [PayoutController::class, 'bulkCreate']);
        Route::post('/admin/payouts/bulk-mark-paid',[PayoutController::class, 'bulkMarkPaid']);
        Route::get('/admin/payouts',                [PayoutController::class, 'adminIndex']);
        Route::post('/admin/payouts',               [PayoutController::class, 'store']);
        Route::patch('/admin/payouts/{payout}/mark-paid', [PayoutController::class, 'markPaid']);
    });
});
