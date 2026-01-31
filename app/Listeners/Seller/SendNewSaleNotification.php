<?php

declare(strict_types=1);

namespace App\Listeners\Seller;

use App\Events\Order\OrderCreated;
use App\Notifications\Seller\NewSaleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewSaleNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        foreach ($event->order->sellers as $seller) {
            $payoutAmount = $event->sellerPayouts->get($seller->id);

            if ($payoutAmount) {
                $seller->notify(new NewSaleNotification(
                    order: $event->order,
                    seller: $seller,
                    payoutAmount: $payoutAmount
                ));
            }
        }
    }
}
