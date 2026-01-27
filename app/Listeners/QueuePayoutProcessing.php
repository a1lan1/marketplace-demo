<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\ProcessPayoutsJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePayoutProcessing implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        dispatch(new ProcessPayoutsJob($event->order, $event->sellerPayouts));
    }
}
