<?php

declare(strict_types=1);

namespace App\Services\Feedback;

use App\Contracts\Services\FeedbackServiceInterface;
use App\Enums\CacheKeyEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

readonly class CachedFeedbackService implements FeedbackServiceInterface
{
    public function __construct(private FeedbackServiceInterface $service) {}

    public function getFeedbacksForTarget(string $type, int $id, int $page = 1): LengthAwarePaginator
    {
        return Cache::tags(['feedbacks'])->flexible(
            sprintf(CacheKeyEnum::FEEDBACKS_TARGET->value, $type, $id, $page),
            [Date::now()->addMinutes(5), Date::now()->addHour()],
            fn (): LengthAwarePaginator => $this->service->getFeedbacksForTarget($type, $id, $page)
        );
    }

    public function getSellerFeedbacks(int $userId, int $page = 1): LengthAwarePaginator
    {
        return Cache::tags(['feedbacks'])->flexible(
            sprintf(CacheKeyEnum::FEEDBACKS_SELLER->value, $userId, $page),
            [Date::now()->addMinutes(5), Date::now()->addHour()],
            fn (): LengthAwarePaginator => $this->service->getSellerFeedbacks($userId, $page)
        );
    }
}
