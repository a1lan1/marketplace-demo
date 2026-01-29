<?php

declare(strict_types=1);

namespace App\Events\Payment;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SellerPayoutProcessed implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $orderId,
        public int $sellerId,
        public int $amount
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.'.$this->sellerId);
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'amount' => $this->amount,
        ];
    }
}
