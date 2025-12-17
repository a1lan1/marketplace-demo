<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\UserActivityController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('user-activities', [UserActivityController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('api.user-activities.store');

Route::middleware('auth:sanctum')
    ->get('user', fn (Request $request) => $request->user());

Route::get('catalog/search', [ApiProductController::class, 'autocomplete'])->name('api.catalog.autocomplete');

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('user/orders', [OrderController::class, 'getUserOrders'])->name('api.user.orders.index');

    Route::prefix('chat')->group(function (): void {
        Route::get('{order}/messages', [ChatController::class, 'getMessages'])->name('api.orders.messages.index');
        Route::post('{order}', [ChatController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('api.chat.store');
    });

    Route::get('products/recommendations', [ProductController::class, 'getRecommendedProducts'])->name('api.products.recommendations');
});
