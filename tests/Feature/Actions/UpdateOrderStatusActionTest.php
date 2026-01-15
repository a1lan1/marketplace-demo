<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\UpdateOrderStatusAction;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Event;

it('updates order status and dispatches event', function (): void {
    // 1. Arrange
    Event::fake();

    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'buyer')->create(['status' => OrderStatusEnum::PENDING]);
    $newStatus = OrderStatusEnum::COMPLETED;

    $orderRepositoryMock = $this->mock(OrderRepositoryInterface::class);
    $orderRepositoryMock->shouldReceive('updateStatus')
        ->once()
        ->with($order, $newStatus);

    $action = new UpdateOrderStatusAction($orderRepositoryMock);

    // 2. Act
    $action->execute($order, $newStatus);

    // 3. Assert
    Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order, $user): bool {
        $broadcastChannel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $broadcastChannel);
        $this->assertEquals('private-App.Models.User.'.$user->id, $broadcastChannel->name);

        return $event->order->id === $order->id;
    });
});
