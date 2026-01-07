<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::prefix('catalog')->group(function (): void {
    Route::get('', [ProductController::class, 'catalog'])->name('products.catalog');
    Route::get('{product}', [ProductController::class, 'show'])
        ->name('products.show')
        ->where('product', '[0-9]+');
});

Route::get('sellers/{seller}', [SellerController::class, 'show'])->name('sellers.show');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('checkout', fn () => Inertia::render('Checkout'))->name('checkout.index');

    Route::prefix('orders')->group(function (): void {
        Route::get('', [OrderController::class, 'index'])->name('orders.index');
        Route::post('', [OrderController::class, 'store'])
            ->middleware(['throttle:5,1', 'idempotency'])
            ->name('orders.store');
        Route::put('{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
    });

    Route::prefix('chat')->group(function (): void {
        Route::get('', [ChatController::class, 'index'])->name('chat.index');
        Route::get('{order}', [ChatController::class, 'show'])->name('chat.show');
    });

    Route::prefix('geo')->name('geo.')->group(function (): void {
        Route::get('dashboard', fn () => Inertia::render('Geo/Dashboard'))->name('dashboard');
        Route::get('map', fn () => Inertia::render('Geo/Map'))->name('map');

        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    });
});

require __DIR__.'/settings.php';
