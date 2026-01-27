<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public Collection $sellerPayouts
    ) {}

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('App.Models.User.'.$this->order->user_id),
        ];

        foreach ($this->order->products as $product) {
            $channels[] = new PrivateChannel('App.Models.User.'.$product->user_id);
        }

        return $channels;
    }
}
