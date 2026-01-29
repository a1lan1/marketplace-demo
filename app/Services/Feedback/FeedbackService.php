<?php

declare(strict_types=1);

namespace App\Services\Feedback;

use App\Contracts\Repositories\FeedbackRepositoryInterface;
use App\Contracts\Services\Feedback\FeedbackableMapInterface;
use App\Contracts\Services\FeedbackServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class FeedbackService implements FeedbackServiceInterface
{
    public function __construct(
        private FeedbackableMapInterface $feedbackableMap,
        private FeedbackRepositoryInterface $feedbackRepository
    ) {}

    public function getFeedbacksForTarget(string $type, int $id, int $page = 1): LengthAwarePaginator
    {
        $modelClass = $this->feedbackableMap->get($type);

        return $this->feedbackRepository->getForEntity($modelClass, $id, 15, $page);
    }

    public function getSellerFeedbacks(int $userId, int $page = 1): LengthAwarePaginator
    {
        return $this->feedbackRepository->getForUser($userId, 15, $page);
    }
}
