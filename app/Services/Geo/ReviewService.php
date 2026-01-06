<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Services\Geo\ReviewServiceInterface;
use App\DTO\Geo\ReviewFilterData;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReviewService implements ReviewServiceInterface
{
    public function getReviewsForUser(User $user, ReviewFilterData $filters, int $page = 1): LengthAwarePaginator
    {
        return Review::query()
            ->select(['id', 'location_id', 'source', 'author_name', 'text', 'rating', 'sentiment', 'published_at'])
            ->whereHas('location', function (Builder $q) use ($user): void {
                $q->where('seller_id', $user->id);
            })
            ->when($filters->locationId, fn (Builder $q) => $q->where('location_id', $filters->locationId))
            ->when($filters->source, fn (Builder $q) => $q->where('source', $filters->source))
            ->when($filters->sentiment, fn (Builder $q) => $q->where('sentiment', $filters->sentiment))
            ->latest('published_at')
            ->paginate(15, ['*'], 'page', $page);
    }
}
