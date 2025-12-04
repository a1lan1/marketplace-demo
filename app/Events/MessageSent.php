<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.'.$this->message->order_id);
    }

    public function broadcastAs(): string
    {
        return 'order.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->loadMissing('user'),
        ];
    }
}
