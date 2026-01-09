<?php

declare(strict_types=1);

namespace App\Actions\Feedback;

use App\Contracts\Repositories\FeedbackRepositoryInterface;
use App\Enums\SentimentEnum;
use App\Events\FeedbackSaved;

class StoreFeedbackSentimentAction
{
    public function __construct(protected FeedbackRepositoryInterface $feedbackRepository) {}

    public function execute(int $feedbackId, SentimentEnum $sentiment): void
    {
        $feedback = $this->feedbackRepository->updateSentiment($feedbackId, $sentiment);

        event(new FeedbackSaved($feedback));
    }
}
