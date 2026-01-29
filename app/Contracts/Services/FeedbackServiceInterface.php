<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FeedbackServiceInterface
{
    public function getFeedbacksForTarget(string $type, int $id, int $page = 1): LengthAwarePaginator;

    public function getSellerFeedbacks(int $userId, int $page = 1): LengthAwarePaginator;
}
