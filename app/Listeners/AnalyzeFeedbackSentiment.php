<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FeedbackSaved;
use App\Jobs\AnalyzeFeedbackSentimentJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnalyzeFeedbackSentiment implements ShouldQueue
{
    public function handle(FeedbackSaved $event): void
    {
        if ($event->feedback->sentiment === null) {
            dispatch(new AnalyzeFeedbackSentimentJob($event->feedback));
        }
    }
}
