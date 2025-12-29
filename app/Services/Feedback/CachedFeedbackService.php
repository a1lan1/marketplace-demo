<?php

declare(strict_types=1);

namespace App\Services\Feedback;

use App\Contracts\FeedbackServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

readonly class CachedFeedbackService implements FeedbackServiceInterface
{
    public function __construct(private FeedbackServiceInterface $service) {}

    public function getFeedbacksForTarget(string $type, int $id, int $page = 1): LengthAwarePaginator
    {
        $key = sprintf('feedbacks_target_%s_%d_page_%s', $type, $id, $page);

        return Cache::tags(['feedbacks'])
            ->remember($key, 3600, fn (): LengthAwarePaginator => $this->service->getFeedbacksForTarget($type, $id, $page));
    }

    public function getSellerFeedbacks(int $userId, int $page = 1): LengthAwarePaginator
    {
        $key = sprintf('feedbacks_seller_%d_page_%s', $userId, $page);

        return Cache::tags(['feedbacks'])
            ->remember($key, 3600, fn (): LengthAwarePaginator => $this->service->getSellerFeedbacks($userId, $page));
    }
}
