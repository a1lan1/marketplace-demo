<?php

declare(strict_types=1);

namespace App\Services\Feedback;

use App\Contracts\FeedbackServiceInterface;
use App\Models\Feedback;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class FeedbackService implements FeedbackServiceInterface
{
    public function __construct(private FeedbackableMap $feedbackableMap) {}

    public function getFeedbacksForTarget(string $type, int $id, int $page = 1): LengthAwarePaginator
    {
        $modelClass = $this->feedbackableMap->get($type);

        return Feedback::query()
            ->forEntity($modelClass, $id)
            ->with('author')
            ->latest()
            ->paginate(15, ['*'], 'page', $page);
    }

    public function getSellerFeedbacks(int $userId, int $page = 1): LengthAwarePaginator
    {
        return Feedback::query()
            ->forUser($userId)
            ->with(['author', 'feedbackable'])
            ->latest()
            ->paginate(15, ['*'], 'page', $page);
    }
}
