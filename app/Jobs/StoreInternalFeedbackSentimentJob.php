<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Feedback\StoreFeedbackSentimentAction;
use App\Enums\SentimentEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreInternalFeedbackSentimentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $feedbackId,
        public SentimentEnum $sentiment
    ) {}

    public function handle(StoreFeedbackSentimentAction $action): void
    {
        $action->execute($this->feedbackId, $this->sentiment);
    }
}
