<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::post('/test/idempotency', function (Request $request) {
        return response()->json(['message' => 'Processed', 'data' => $request->all()]);
    })->middleware(['auth', 'idempotency']);
});

test('idempotency prevents double processing', function (): void {
    $user = User::factory()->create();
    $key = 'test-key-123';

    $response1 = $this->actingAs($user)
        ->postJson('/test/idempotency', ['foo' => 'bar'], ['Idempotency-Key' => $key]);

    $response1->assertStatus(200)
        ->assertJson(['message' => 'Processed']);

    $response2 = $this->actingAs($user)
        ->postJson('/test/idempotency', ['foo' => 'bar'], ['Idempotency-Key' => $key]);

    $response2->assertStatus(200)
        ->assertHeader('Idempotent-Replayed', 'true')
        ->assertJson(['message' => 'Processed']);
});

test('concurrent requests are locked', function (): void {
    $user = User::factory()->create();
    $key = 'concurrent-key-123';

    $cacheKey = sprintf('idempotency:%d:%s:lock', $user->id, $key);
    Cache::lock($cacheKey, 10)->get();

    $response = $this->actingAs($user)
        ->postJson('/test/idempotency', ['foo' => 'bar'], ['Idempotency-Key' => $key]);

    $response->assertStatus(409)
        ->assertJson(['error' => 'Request is being processed']);
});

test('different keys are processed separately', function (): void {
    $user = User::factory()->create();

    $response1 = $this->actingAs($user)
        ->postJson('/test/idempotency', ['val' => 1], ['Idempotency-Key' => 'key-1']);

    $response2 = $this->actingAs($user)
        ->postJson('/test/idempotency', ['val' => 2], ['Idempotency-Key' => 'key-2']);

    $response1->assertStatus(200);
    $response2->assertStatus(200);

    $this->assertFalse($response2->headers->has('Idempotent-Replayed'));
});

test('concurrent stock updates', function (): void {
    $user = User::factory()->withBuyerRole()->create();
    $user->balance = 100000;
    $user->save();

    $product = Product::factory()->create(['stock' => 10, 'price' => 100]);

    $key = 'order-key-123';

    $response1 = $this->actingAs($user)
        ->postJson(route('orders.store'), [
            'cart' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ], ['Idempotency-Key' => $key]);

    $response1->assertRedirect(route('orders.index'));

    $response2 = $this->actingAs($user)
        ->postJson(route('orders.store'), [
            'cart' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ], ['Idempotency-Key' => $key]);

    $response2->assertRedirect(route('orders.index'));
    $response2->assertHeader('Idempotent-Replayed', 'true');

    $this->assertDatabaseCount('orders', 1);
    $this->assertEquals(9, $product->fresh()->stock);
});

test('missing idempotency key returns bad request', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/test/idempotency', ['foo' => 'bar']);

    $response->assertStatus(400)
        ->assertJson(['error' => 'Idempotency-Key header is required']);
});
