<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Services\Geo\ReviewServiceInterface;
use App\DTO\Geo\ReviewFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService implements ReviewServiceInterface
{
    public function __construct(protected ReviewRepositoryInterface $reviewRepository) {}

    public function getReviewsForUser(User $user, ReviewFilterData $filters, int $page = 1): LengthAwarePaginator
    {
        return $this->reviewRepository->getForUserWithFilters($user, $filters, 15, $page);
    }
}
