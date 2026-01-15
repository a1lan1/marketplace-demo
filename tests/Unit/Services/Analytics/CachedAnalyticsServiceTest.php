<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Analytics;

use App\Contracts\Services\Analytics\AnalyticsServiceInterface;
use App\Enums\CacheKeyEnum;
use App\Services\Analytics\CachedAnalyticsService;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Mockery;

test('get total revenue in usd returns cached result', function (): void {
    // Arrange
    $innerService = Mockery::mock(AnalyticsServiceInterface::class);
    $revenue = Money::USD(1000);

    Cache::shouldReceive('tags')->with(['analytics'])->andReturnSelf();
    Cache::shouldReceive('flexible')
        ->once()
        ->with(
            CacheKeyEnum::ANALYTICS_TOTAL_REVENUE->value,
            Mockery::type('array'),
            Mockery::type('closure')
        )
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getTotalRevenueInUsd')
        ->once()
        ->andReturn($revenue);

    $cachedService = new CachedAnalyticsService($innerService);

    // Act
    $result = $cachedService->getTotalRevenueInUsd();

    // Assert
    expect($result)->toBe($revenue);
});

test('get sales by currency returns cached result', function (): void {
    // Arrange
    $innerService = Mockery::mock(AnalyticsServiceInterface::class);
    $sales = new Collection([['currency' => 'USD', 'total' => 1000]]);

    Cache::shouldReceive('tags')->with(['analytics'])->andReturnSelf();
    Cache::shouldReceive('flexible')
        ->once()
        ->with(
            CacheKeyEnum::ANALYTICS_SALES_BY_CURRENCY->value,
            Mockery::type('array'),
            Mockery::type('closure')
        )
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getSalesByCurrency')
        ->once()
        ->andReturn($sales);

    $cachedService = new CachedAnalyticsService($innerService);

    // Act
    $result = $cachedService->getSalesByCurrency();

    // Assert
    expect($result)->toBe($sales);
});
