<?php

declare(strict_types=1);

namespace App\Services\Feedback;

use App\Contracts\FeedbackServiceInterface;
use App\Enums\CacheKeyEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

readonly class CachedFeedbackService implements FeedbackServiceInterface
{
    public function __construct(private FeedbackServiceInterface $service) {}

    public function getFeedbacksForTarget(string $type, int $id, int $page = 1): LengthAwarePaginator
    {
        return Cache::tags(['feedbacks'])
            ->remember(
                sprintf(CacheKeyEnum::FEEDBACKS_TARGET->value, $type, $id, $page),
                3600,
                fn (): LengthAwarePaginator => $this->service->getFeedbacksForTarget($type, $id, $page)
            );
    }

    public function getSellerFeedbacks(int $userId, int $page = 1): LengthAwarePaginator
    {
        return Cache::tags(['feedbacks'])
            ->remember(
                sprintf(CacheKeyEnum::FEEDBACKS_SELLER->value, $userId, $page),
                3600,
                fn (): LengthAwarePaginator => $this->service->getSellerFeedbacks($userId, $page)
            );
    }
}
