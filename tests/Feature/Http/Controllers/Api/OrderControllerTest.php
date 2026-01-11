<?php

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\mock;

it('returns user orders', function (): void {
    $user = User::factory()->create();
    actingAs($user, 'sanctum');

    $orders = Order::factory()->count(2)->make();

    mock(OrderServiceInterface::class, function ($mock) use ($orders): void {
        $mock->shouldReceive('getOrdersForUser')->once()->andReturn($orders);
    });

    getJson(route('api.user.orders.index'))
        ->assertOk()
        ->assertJson($orders->map(fn ($order): array => ['id' => $order->id])->all());
});
