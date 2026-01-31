<?php

declare(strict_types=1);

namespace App\Events\Order;

use App\Contracts\LoggableEvent;
use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OrderCreated implements LoggableEvent, ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  Collection<int, Money>  $sellerPayouts
     */
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

        foreach ($this->order->sellers as $seller) {
            $channels[] = new PrivateChannel('App.Models.User.'.$seller->id);
        }

        return $channels;
    }

    public function getPerformedOn(): ?Model
    {
        return $this->order;
    }

    public function getCausedBy(): ?User
    {
        return $this->order->buyer;
    }

    public function getDescription(): string
    {
        return 'Order created successfully';
    }

    public function getProperties(): array
    {
        return [];
    }
}
