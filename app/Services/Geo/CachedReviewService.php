<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Services\Geo\ReviewServiceInterface;
use App\DTO\Geo\ReviewFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

readonly class CachedReviewService implements ReviewServiceInterface
{
    public function __construct(private ReviewServiceInterface $service) {}

    public function getReviewsForUser(User $user, ReviewFilterData $filters, int $page = 1): LengthAwarePaginator
    {
        $key = sprintf('reviews_user_%d_%s_page_%d', $user->id, $filters->cacheKey(), $page);

        return Cache::tags(['reviews'])
            ->remember($key, 3600, fn (): LengthAwarePaginator => $this->service->getReviewsForUser($user, $filters, $page));
    }
}
