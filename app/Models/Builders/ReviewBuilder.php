<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\DTO\Geo\ReviewFilterData;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of Review
 *
 * @extends Builder<TModelClass>
 */
class ReviewBuilder extends Builder
{
    public function forUser(User $user): self
    {
        return $this->whereHas('location', function (Builder $q) use ($user): void {
            $q->where('seller_id', $user->id);
        });
    }

    public function applyFilters(ReviewFilterData $filters): self
    {
        return $this
            ->when($filters->locationId, fn (Builder $q) => $q->where('location_id', $filters->locationId))
            ->when($filters->source, fn (Builder $q) => $q->where('source', $filters->source))
            ->when($filters->sentiment, fn (Builder $q) => $q->where('sentiment', $filters->sentiment));
    }

    public function forLocation(?int $locationId): self
    {
        return $this->when($locationId, fn (Builder $q) => $q->where('location_id', $locationId));
    }
}
