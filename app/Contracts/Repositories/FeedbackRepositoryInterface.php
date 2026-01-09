<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\FeedbackData;
use App\Enums\SentimentEnum;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FeedbackRepositoryInterface
{
    public function getForEntity(string $modelClass, int $entityId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function getForUser(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function store(User $author, string $feedbackableClass, FeedbackData $data, bool $isVerified): Feedback;

    public function existsForUserAndEntity(int $userId, string $feedbackableClass, int $feedbackableId): bool;

    public function updateSentiment(int $feedbackId, SentimentEnum $sentiment): Feedback;

    public function getSentimentCountsForUser(int $userId): Collection;
}
