<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Events\Order\OrderCreated;
use App\Notifications\Order\OrderConfirmationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderConfirmationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->loadMissing('buyer');

        $order->buyer->notify(new OrderConfirmationNotification(
            order: $order,
            customer: $order->buyer
        ));
    }
}
