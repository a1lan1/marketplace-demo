<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\RecommendationServiceInterface;
use App\Enums\CacheKeyEnum;
use App\Services\CachedRecommendationService;
use Illuminate\Support\Facades\Cache;
use Mockery;

test('get recommendations returns cached result', function (): void {
    // Arrange
    $innerService = Mockery::mock(RecommendationServiceInterface::class);
    $userId = 1;
    $recommendations = [1, 2, 3];

    Cache::shouldReceive('tags')->with(['recommendations'])->andReturnSelf();
    Cache::shouldReceive('flexible')
        ->once()
        ->with(
            sprintf(CacheKeyEnum::RECOMMENDATIONS_USER->value, $userId),
            Mockery::type('array'),
            Mockery::type('closure')
        )
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getRecommendations')
        ->once()
        ->with($userId)
        ->andReturn($recommendations);

    $cachedService = new CachedRecommendationService($innerService);

    // Act
    $result = $cachedService->getRecommendations($userId);

    // Assert
    expect($result)->toBe($recommendations);
});
