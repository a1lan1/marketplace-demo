<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Chat;

use App\Actions\Chat\SendMessageAction;
use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Event;

it('creates a message and dispatches an event', function (): void {
    // 1. Arrange
    Event::fake();

    $order = Order::factory()->create();
    $sender = User::factory()->create();
    $messageContent = 'Hello, this is a test message.';

    $messageRepositoryMock = $this->mock(MessageRepositoryInterface::class);
    $messageRepositoryMock->shouldReceive('createForOrder')
        ->once()
        ->andReturnUsing(function (Order $o, User $s, string $c) {
            return Message::factory()->create([
                'order_id' => $o->id,
                'user_id' => $s->id,
                'message' => $c,
            ]);
        });

    $action = new SendMessageAction($messageRepositoryMock);

    // 2. Act
    $message = $action->execute($order, $sender, $messageContent);

    // 3. Assert
    $this->assertDatabaseHas('messages', [
        'order_id' => $order->id,
        'user_id' => $sender->id,
        'message' => $messageContent,
    ]);

    expect($message)->not()->toBeNull()
        ->and($message->order_id)->toBe($order->id)
        ->and($message->user_id)->toBe($sender->id);

    Event::assertDispatched(MessageSent::class, function ($event) use ($message): bool {
        return $event->message->id === $message->id && $event->message->relationLoaded('user');
    });
});
