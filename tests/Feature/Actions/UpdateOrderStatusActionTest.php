<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\UpdateOrderStatusAction;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Event;

it('updates order status and dispatches event', function (): void {
    // 1. Arrange
    Event::fake();

    $order = Order::factory()->create(['status' => OrderStatusEnum::PENDING]);
    $newStatus = OrderStatusEnum::COMPLETED;

    $orderRepositoryMock = $this->mock(OrderRepositoryInterface::class);
    $orderRepositoryMock->shouldReceive('updateStatus')
        ->once()
        ->with($order, $newStatus);

    $action = new UpdateOrderStatusAction($orderRepositoryMock);

    // 2. Act
    $action->execute($order, $newStatus);

    // 3. Assert
    Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order): bool {
        return $event->order->id === $order->id;
    });
});
