<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\DTO\Geo\ReviewData;
use App\DTO\Geo\ReviewFilterData;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function getForUserWithFilters(User $user, ReviewFilterData $filters, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return Review::query()
            ->select(['id', 'location_id', 'source', 'author_name', 'text', 'rating', 'sentiment', 'published_at'])
            ->forUser($user)
            ->applyFilters($filters)
            ->latest('published_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @return Collection<int, Review>
     */
    public function getForUserAndLocation(User $user, ?int $locationId = null): Collection
    {
        return Review::query()
            ->select(['id', 'location_id', 'source', 'author_name', 'text', 'rating', 'sentiment', 'published_at', 'created_at'])
            ->forUser($user)
            ->forLocation($locationId)
            ->get();
    }

    public function updateOrCreate(ReviewData $data): Review
    {
        return Review::updateOrCreate(
            [
                'external_id' => $data->externalId,
                'source' => $data->source,
            ],
            $data->toArray()
        );
    }
}
