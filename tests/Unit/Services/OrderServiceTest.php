<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->orderService = new OrderService;
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
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBeGreaterThanOrEqual(2);
});

test('get orders for user returns own orders for regular user', function (): void {
    // Arrange
    $user = User::factory()->create();
    Order::factory()->for($user, 'buyer')->create();
    Order::factory()->create();

    // Act
    $result = $this->orderService->getOrdersForUser($user);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(1)
        ->and($result->first()->user_id)->toBe($user->id);
});
