<?php

declare(strict_types=1);

namespace App\Services\Feedback;

use App\Contracts\FeedbackServiceInterface;
use App\Models\Feedback;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;

readonly class FeedbackService implements FeedbackServiceInterface
{
    public function __construct(private FeedbackableMap $feedbackableMap) {}

    public function getFeedbacksForTarget(string $type, int $id, int $page = 1): LengthAwarePaginator
    {
        $modelClass = $this->feedbackableMap->get($type);

        return Feedback::query()
            ->forEntity($modelClass, $id)
            ->select(['id', 'user_id', 'feedbackable_type', 'feedbackable_id', 'rating', 'comment', 'sentiment', 'is_verified_purchase', 'created_at'])
            ->with([
                'author' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->latest()
            ->paginate(15, ['*'], 'page', $page);
    }

    public function getSellerFeedbacks(int $userId, int $page = 1): LengthAwarePaginator
    {
        return Feedback::query()
            ->forUser($userId)
            ->select(['id', 'user_id', 'feedbackable_type', 'feedbackable_id', 'rating', 'comment', 'sentiment', 'is_verified_purchase', 'created_at'])
            ->with([
                'author' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
                'feedbackable',
            ])
            ->latest()
            ->paginate(15, ['*'], 'page', $page);
    }
}
