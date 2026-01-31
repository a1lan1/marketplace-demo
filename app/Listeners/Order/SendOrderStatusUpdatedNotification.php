<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Events\Order\OrderStatusChanged;
use App\Notifications\Order\OrderStatusUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderStatusUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order->loadMissing('buyer');

        $order->buyer->notify(new OrderStatusUpdatedNotification($order));
    }
}
