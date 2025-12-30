<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Product;
use App\Models\User;
use Cknow\Money\Money;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

beforeEach(function (): void {
    $this->buyer = User::factory()->withBuyerRole()->create(['balance' => Money::USD(100000)]);
    actingAs($this->buyer);
});

test('user can place order', function (): void {
    // Arrange
    $product = Product::factory()->create(['price' => Money::USD(10000), 'stock' => 10]);

    // Act
    $response = post(route('orders.store'), [
        'cart' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ]);

    // Assert
    $response->assertRedirect(route('orders.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('orders', [
        'user_id' => $this->buyer->id,
        'total_amount' => 20000,
        'status' => OrderStatusEnum::PENDING->value,
    ]);
});

test('user cannot order non-existent product', function (): void {
    // Act
    $response = post(route('orders.store'), [
        'cart' => [
            ['product_id' => 99999, 'quantity' => 1],
        ],
    ]);

    // Assert
    $response->assertSessionHasErrors('cart.0.product_id');
});
