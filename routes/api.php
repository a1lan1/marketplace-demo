<?php

use App\Http\Controllers\Api\UserActivityController;
use Illuminate\Support\Facades\Route;

/*
|-------------------------------------------------------------------------
| API Routes
|-------------------------------------------------------------------------
| These routes are loaded by the framework within the "api" middleware
| group and prefixed with /api. They are stateless and rate-limited
| according to your application's configuration.
*/

Route::post('/user-activities', [UserActivityController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('api.user-activities.store');
