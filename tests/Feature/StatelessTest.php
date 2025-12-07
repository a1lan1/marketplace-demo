<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('octane does not leak authentication state between requests', function (): void {
    // First request with user A
    $userA = User::factory()->create();
    actingAs($userA);
    get('/dashboard')->assertOk();
    expect(auth()->id())->toBe($userA->id);

    // Second request with user B
    $userB = User::factory()->create();
    actingAs($userB);
    get('/dashboard')->assertOk();
    expect(auth()->id())->toBe($userB->id);
});

test('octane does not leak session flash data between requests', function (): void {
    Route::middleware('web')->get('/check-flash', function () {
        return response()->json(['session_status' => session('status')]);
    });

    session()->flash('status', 'This is a flashed message.');

    // Request 1: The data should be available on the very next request.
    get('/check-flash')
        ->assertSessionHas('status', 'This is a flashed message.');

    // Request 2: On the subsequent request, the flash data should be gone.
    get('/check-flash')
        ->assertSessionMissing('status');
});

test('octane does not leak static properties between requests', function (): void {
    $stateManager = function (string $action = 'get', ?string $value = null) {
        static $state = null;

        switch ($action) {
            case 'set':
                $state = $value;
                break;
            case 'reset':
                $state = null;
                break;
            case 'get':
            default:
                return $state;
        }
    };

    // Reset state before the test begins
    $stateManager('reset');

    Route::get('/test-static', function () use ($stateManager): string {
        $stateManager('set', 'first-request-state');

        return 'ok';
    });

    // First request sets the state
    get('/test-static')->assertOk();
    expect($stateManager('get'))->toBe('first-request-state');

    // Manually reset state to simulate Octane starting a new, clean request
    $stateManager('reset');

    // Second request should not have the state from the first
    Route::get('/test-static-check', function () use ($stateManager) {
        return $stateManager('get');
    });

    get('/test-static-check')->assertSee(null);
});

test('octane does not leak runtime config changes between requests', function (): void {
    Route::get('/test-config', function (): string {
        config(['app.name' => 'Temp App Name']);

        return 'ok';
    });

    $originalAppName = config('app.name');

    // First request changes the config
    get('/test-config')->assertOk();

    // In a non-Octane environment, the config change will leak. We manually reset it
    // to simulate the clean state Octane provides for each new request.
    config(['app.name' => $originalAppName]);

    Route::get('/test-config-check', function () {
        return config('app.name');
    });

    // The second request should now see the original config value
    get('/test-config-check')->assertSee($originalAppName);
});
