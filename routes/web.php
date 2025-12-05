<?php

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
});

require __DIR__.'/settings.php';
