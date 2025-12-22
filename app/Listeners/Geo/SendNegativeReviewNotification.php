<?php

declare(strict_types=1);

namespace App\Listeners\Geo;

use App\Events\Geo\NewNegativeReviewReceived;
use App\Mail\Geo\NegativeReviewReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendNegativeReviewNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NewNegativeReviewReceived $event): void
    {
        $seller = $event->review->location->seller;

        Mail::to($seller->email)->send(new NegativeReviewReceived($event->review));
    }
}
