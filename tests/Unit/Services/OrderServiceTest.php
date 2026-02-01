<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\CartItemDTO;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Cknow\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\DataCollection;

beforeEach(function (): void {
    $this->orderService = resolve(OrderService::class);
});

test('get user orders returns paginated list', function (): void {
    // Arrange
    $user = User::factory()->create();
    Order::factory()->count(3)->create(['user_id' => $user->id]);

    // Act
    $result = $this->orderService->getUserOrders($user, 15);

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(3);
});

test('get orders for user returns all orders for admin', function (): void {
    // Arrange
    $admin = User::factory()->withAdminRole()->create();

    Order::factory()->count(2)->create();

    // Act
    $result = $this->orderService->getOrdersForUser($admin);

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBeGreaterThanOrEqual(2);
});

test('get orders for user returns own orders for regular user', function (): void {
    // Arrange
    $user = User::factory()->create();
    Order::factory()->for($user, 'buyer')->create();
    Order::factory()->create();

    // Act
    $result = $this->orderService->getOrdersForUser($user);

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(1)
        ->and($result->items()[0]->user_id)->toBe($user->id);
});

test('create order attaches products correctly', function (): void {
    // Arrange
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1000]); // $10.00

    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO($product->id, 2),
    ]);

    $products = new Collection([$product]);
    $totalAmount = Money::USD(2000);

    // Act
    $order = $this->orderService->createOrder($buyer, $totalAmount, $cart, $products);

    // Assert
    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->total_amount->getAmount())->toBe('2000');

    $this->assertDatabaseHas('order_product', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 1000,
    ]);
});
