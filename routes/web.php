<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
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

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('checkout', fn () => Inertia::render('Checkout'))->name('checkout.index');

    Route::prefix('orders')->group(function (): void {
        Route::get('', [OrderController::class, 'index'])->name('orders.index');
        Route::post('', [OrderController::class, 'store'])->name('orders.store');
        Route::put('{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
    });
});

require __DIR__.'/settings.php';
