<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\FeedbackRepositoryInterface;
use App\DTO\FeedbackData;
use App\Enums\SentimentEnum;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FeedbackRepository implements FeedbackRepositoryInterface
{
    public function getForEntity(string $modelClass, int $entityId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return Feedback::query()
            ->select(['id', 'user_id', 'feedbackable_type', 'feedbackable_id', 'rating', 'comment', 'sentiment', 'is_verified_purchase', 'created_at'])
            ->forEntity($modelClass, $entityId)
            ->withAuthorDetails()
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getForUser(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return Feedback::query()
            ->select(['id', 'user_id', 'feedbackable_type', 'feedbackable_id', 'rating', 'comment', 'sentiment', 'is_verified_purchase', 'created_at'])
            ->forUser($userId)
            ->withAuthorDetails()
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function store(User $author, string $feedbackableClass, FeedbackData $data, bool $isVerified): Feedback
    {
        return Feedback::create([
            'user_id' => $author->id,
            'feedbackable_type' => $feedbackableClass,
            'feedbackable_id' => $data->feedbackableId,
            'rating' => $data->rating,
            'comment' => $data->comment,
            'is_verified_purchase' => $isVerified,
        ]);
    }

    public function existsForUserAndEntity(int $userId, string $feedbackableClass, int $feedbackableId): bool
    {
        return Feedback::query()
            ->where('user_id', $userId)
            ->where('feedbackable_type', $feedbackableClass)
            ->where('feedbackable_id', $feedbackableId)
            ->exists();
    }

    public function updateSentiment(int $feedbackId, SentimentEnum $sentiment): Feedback
    {
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->update(['sentiment' => $sentiment]);

        return $feedback;
    }

    public function getSentimentCountsForUser(int $userId): Collection
    {
        return Feedback::query()
            ->forUser($userId)
            ->selectRaw('count(*) as total, sentiment')
            ->groupBy('sentiment')
            ->pluck('total', 'sentiment');
    }
}
