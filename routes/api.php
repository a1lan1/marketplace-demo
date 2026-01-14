<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\Geo\LocationController;
use App\Http\Controllers\Api\Geo\ResponseTemplateController;
use App\Http\Controllers\Api\Geo\ReviewController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserActivityController;
use Illuminate\Support\Facades\Route;

Route::post('user-activities', [UserActivityController::class, 'store'])
    ->middleware('throttle:activities')
    ->name('api.user-activities.store');

Route::get('catalog/search', [ProductController::class, 'autocomplete'])
    ->middleware('throttle:catalog')
    ->name('api.catalog.autocomplete');

Route::get('{type}/{id}/feedbacks', [FeedbackController::class, 'index'])
    ->middleware('throttle:feedbacks')
    ->name('api.feedbacks.index');

Route::get('currency/rates', [CurrencyController::class, 'rates'])
    ->middleware('throttle:currency')
    ->name('currency.rates');

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('user', CurrentUserController::class);
    Route::get('user/orders', [OrderController::class, 'getUserOrders'])->name('api.user.orders.index');

    Route::prefix('chat')->group(function (): void {
        Route::get('{order}/messages', [ChatController::class, 'getMessages'])->name('api.orders.messages.index');
        Route::post('{order}', [ChatController::class, 'store'])
            ->middleware('throttle:chat')
            ->name('api.chat.store');
    });

    Route::get('products/recommendations', [ProductController::class, 'getRecommendedProducts'])->name('api.products.recommendations');

    Route::prefix('geo')->name('api.geo.')->group(function (): void {
        Route::apiResource('locations', LocationController::class);
        Route::apiResource('response-templates', ResponseTemplateController::class);

        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('metrics', [ReviewController::class, 'metrics'])->name('metrics');
    });

    Route::get('feedbacks', [FeedbackController::class, 'list'])->name('api.feedbacks.list');
    Route::post('feedbacks', [FeedbackController::class, 'store'])
        ->middleware('throttle:feedbacks')
        ->name('api.feedbacks.store');
});
