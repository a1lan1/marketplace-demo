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
    public function getReviewsForUser(User $user, ReviewFilterData $filters): LengthAwarePaginator
    {
        return Review::query()
            ->whereHas('location', function (Builder $q) use ($user): void {
                $q->where('seller_id', $user->id);
            })
            ->when($filters->locationId, fn (Builder $q) => $q->where('location_id', $filters->locationId))
            ->when($filters->source, fn (Builder $q) => $q->where('source', $filters->source))
            ->when($filters->sentiment, fn (Builder $q) => $q->where('sentiment', $filters->sentiment))
            ->latest('published_at')
            ->paginate();
    }
}
