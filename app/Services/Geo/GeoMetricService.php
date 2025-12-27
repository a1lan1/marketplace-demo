<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GeoMetricService
{
    /**
     * @return array<string, mixed>
     */
    public function calculateForUser(User $user, ?int $locationId = null): array
    {
        $key = sprintf('geo_metrics_user_%d_loc_', $user->id).($locationId ?? 'all');

        return Cache::tags(['reviews', 'locations'])->remember($key, 3600, function () use ($user, $locationId): array {
            $baseQuery = Review::query()
                ->whereHas('location', function (Builder $q) use ($user): void {
                    $q->where('seller_id', $user->id);
                })
                ->when($locationId, fn (Builder $q) => $q->where('location_id', $locationId));

            $averageRating = (float) $baseQuery->clone()->avg('rating');
            $totalReviews = $baseQuery->clone()->count();

            $sentimentCounts = $baseQuery->clone()
                ->select('sentiment', DB::raw('count(*) as count'))
                ->groupBy('sentiment')
                ->pluck('count', 'sentiment');

            $sourceCounts = $baseQuery->clone()
                ->select('source', DB::raw('count(*) as count'))
                ->groupBy('source')
                ->pluck('count', 'source');

            $ratingDynamics = $baseQuery->clone()
                ->select(DB::raw('DATE(published_at) as date'), DB::raw('AVG(rating) as average_rating'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            return [
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'sentiment_distribution' => $sentimentCounts,
                'source_distribution' => $sourceCounts,
                'rating_dynamics' => $ratingDynamics,
            ];
        });
    }
}
