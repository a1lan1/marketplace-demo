<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Services\Geo\ReviewServiceInterface;
use App\DTO\Geo\ReviewFilterData;
use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ReviewService implements ReviewServiceInterface
{
    public function getReviewsForUser(User $user, ReviewFilterData $filters): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $key = sprintf('reviews_user_%d_loc_', $user->id).($filters->locationId ?? 'all').
            '_src_'.($filters->source instanceof ReviewSourceEnum ? $filters->source->value : 'all').
            '_sent_'.($filters->sentiment instanceof SentimentEnum ? $filters->sentiment->value : 'all').
            ('_page_'.$page);

        return Cache::tags(['reviews'])->remember($key, 3600, function () use ($user, $filters): LengthAwarePaginator {
            return Review::query()
                ->whereHas('location', function (Builder $q) use ($user): void {
                    $q->where('seller_id', $user->id);
                })
                ->when($filters->locationId, fn (Builder $q) => $q->where('location_id', $filters->locationId))
                ->when($filters->source, fn (Builder $q) => $q->where('source', $filters->source))
                ->when($filters->sentiment, fn (Builder $q) => $q->where('sentiment', $filters->sentiment))
                ->latest('published_at')
                ->paginate();
        });
    }
}
