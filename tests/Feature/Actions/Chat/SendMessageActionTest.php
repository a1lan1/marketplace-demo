<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Chat;

use App\Actions\Chat\SendMessageAction;
use App\Events\MessageSent;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Event;

it('creates a message and dispatches an event', function (): void {
    // 1. Arrange
    Event::fake();

    $order = Order::factory()->create();
    $sender = User::factory()->create();
    $messageContent = 'Hello, this is a test message.';
    $action = new SendMessageAction;

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
