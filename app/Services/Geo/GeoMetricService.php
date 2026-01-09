<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GeoMetricService
{
    public function __construct(protected ReviewRepositoryInterface $reviewRepository) {}

    /**
     * @return array<string, mixed>
     */
    public function calculateForUser(User $user, ?int $locationId = null): array
    {
        $key = sprintf('geo_metrics_user_%d_loc_', $user->id).($locationId ?? 'all');

        return Cache::tags(['reviews', 'locations'])->remember($key, 3600, function () use ($user, $locationId): array {
            $reviews = $this->reviewRepository->getForUserAndLocation($user, $locationId);

            $averageRating = (float) $reviews->avg('rating');
            $totalReviews = $reviews->count();

            $sentimentCounts = $reviews
                ->groupBy('sentiment')
                ->map->count();

            $sourceCounts = $reviews
                ->groupBy('source')
                ->map->count();

            $ratingDynamics = $reviews
                ->sortBy('published_at')
                ->groupBy(fn (Review $review) => $review->published_at->format('Y-m-d'))
                ->map(function (Collection $group): array {
                    /** @var Review $firstReview */
                    $firstReview = $group->first();
                    /** @var float $avgRating */
                    $avgRating = $group->avg('rating');

                    return [
                        'date' => $firstReview->published_at->format('Y-m-d'),
                        'average_rating' => $avgRating,
                    ];
                })
                ->values();

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
