<?php

declare(strict_types=1);

namespace App\Actions\Feedback;

use App\Enums\SentimentEnum;
use App\Events\FeedbackSaved;
use App\Models\Feedback;

class StoreFeedbackSentimentAction
{
    public function execute(int $feedbackId, SentimentEnum $sentiment): void
    {
        $feedback = Feedback::findOrFail($feedbackId);

        $feedback->update(['sentiment' => $sentiment]);

        event(new FeedbackSaved($feedback));
    }
}
