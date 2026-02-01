<?php

use App\Contracts\Services\OrderServiceInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\mock;

it('returns user orders', function (): void {
    $user = User::factory()->create();
    actingAs($user, 'sanctum');

    $orders = Order::factory()->count(2)->make();
    $paginator = new LengthAwarePaginator($orders, 2, 10);

    mock(OrderServiceInterface::class, function ($mock) use ($paginator): void {
        $mock->shouldReceive('getOrdersForUser')->once()->andReturn($paginator);
    });

    getJson(route('api.user.orders.index'))
        ->assertOk()
        ->assertJson(['data' => $orders->map(fn ($order): array => ['id' => $order->id])->all()]);
});
