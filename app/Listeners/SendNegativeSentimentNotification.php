<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\NegativeSentimentDetected;
use App\Mail\Geo\NegativeReviewReceived;
use App\Mail\NegativeFeedbackReceived;
use App\Models\Feedback;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendNegativeSentimentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NegativeSentimentDetected $event): void
    {
        $model = $event->model;
        $recipient = $model->getRecipient();

        if (! $recipient instanceof User) {
            return;
        }

        $mailable = match (true) {
            $model instanceof Feedback => new NegativeFeedbackReceived($model),
            $model instanceof Review => new NegativeReviewReceived($model),
            default => null,
        };

        if ($mailable !== null) {
            Mail::to($recipient->email)->send($mailable);
        }
    }
}
