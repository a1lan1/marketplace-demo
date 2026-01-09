<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\Geo\ReviewData;
use App\DTO\Geo\ReviewFilterData;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface
{
    public function getForUserWithFilters(User $user, ReviewFilterData $filters, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    /**
     * @return Collection<int, Review>
     */
    public function getForUserAndLocation(User $user, ?int $locationId = null): Collection;

    public function updateOrCreate(ReviewData $data): Review;
}
