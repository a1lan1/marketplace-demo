<?php

use App\Contracts\OrderServiceInterface;
use App\Models\User;
use Illuminate\Support\Collection;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\mock;

it('returns user orders', function (): void {
    $user = User::factory()->create();
    actingAs($user, 'sanctum');

    $orders = new Collection(['order1', 'order2']);

    mock(OrderServiceInterface::class, function ($mock) use ($orders): void {
        $mock->shouldReceive('getOrdersForUser')->once()->andReturn($orders);
    });

    getJson(route('api.user.orders.index'))
        ->assertOk()
        ->assertJson($orders->all());
});
