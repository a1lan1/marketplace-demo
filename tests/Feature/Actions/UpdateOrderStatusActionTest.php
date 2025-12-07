<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Event;

it('updates order status and dispatches event', function (): void {
    // 1. Arrange
    Event::fake();

    $order = Order::factory()->create(['status' => OrderStatusEnum::PENDING]);
    $action = new UpdateOrderStatusAction;
    $newStatus = OrderStatusEnum::COMPLETED;

    // 2. Act
    $action->execute($order, $newStatus);

    // 3. Assert
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => $newStatus->value,
    ]);

    Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order): bool {
        return $event->order->id === $order->id;
    });
});
