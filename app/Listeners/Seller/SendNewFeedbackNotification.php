<?php

declare(strict_types=1);

namespace App\Listeners\Seller;

use App\Events\FeedbackSaved;
use App\Models\Product;
use App\Notifications\Seller\NewFeedbackNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewFeedbackNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FeedbackSaved $event): void
    {
        $feedback = $event->feedback->loadMissing('feedbackable.seller');
        $feedbackable = $feedback->feedbackable;

        if ($feedbackable instanceof Product) {
            $feedbackable->seller->notify(new NewFeedbackNotification($feedback));
        }
    }
}
