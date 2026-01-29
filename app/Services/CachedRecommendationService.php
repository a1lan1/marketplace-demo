<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\RecommendationServiceInterface;
use App\Enums\CacheKeyEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

readonly class CachedRecommendationService implements RecommendationServiceInterface
{
    public function __construct(private RecommendationServiceInterface $service) {}

    /**
     * Get recommended product IDs for a given user.
     *
     * @return array<int>
     */
    public function getRecommendations(int $userId): array
    {
        return Cache::tags(['recommendations'])->flexible(
            sprintf(CacheKeyEnum::RECOMMENDATIONS_USER->value, $userId),
            [Date::now()->addMinutes(10), Date::now()->addHour()],
            fn (): array => $this->service->getRecommendations($userId)
        );
    }
}
