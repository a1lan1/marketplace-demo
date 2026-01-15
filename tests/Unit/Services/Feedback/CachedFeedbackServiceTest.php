<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Feedback;

use App\Contracts\FeedbackServiceInterface;
use App\Enums\CacheKeyEnum;
use App\Services\Feedback\CachedFeedbackService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Mockery;

test('get feedbacks for target returns cached result', function (): void {
    // Arrange
    $innerService = Mockery::mock(FeedbackServiceInterface::class);
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $type = 'product';
    $id = 1;
    $page = 1;

    Cache::shouldReceive('tags')->with(['feedbacks'])->andReturnSelf();
    Cache::shouldReceive('flexible')
        ->once()
        ->with(
            sprintf(CacheKeyEnum::FEEDBACKS_TARGET->value, $type, $id, $page),
            Mockery::type('array'),
            Mockery::type('closure')
        )
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getFeedbacksForTarget')
        ->once()
        ->with($type, $id, $page)
        ->andReturn($paginator);

    $cachedService = new CachedFeedbackService($innerService);

    // Act
    $result = $cachedService->getFeedbacksForTarget($type, $id, $page);

    // Assert
    expect($result)->toBe($paginator);
});

test('get seller feedbacks returns cached result', function (): void {
    // Arrange
    $innerService = Mockery::mock(FeedbackServiceInterface::class);
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $userId = 1;
    $page = 1;

    Cache::shouldReceive('tags')->with(['feedbacks'])->andReturnSelf();
    Cache::shouldReceive('flexible')
        ->once()
        ->with(
            sprintf(CacheKeyEnum::FEEDBACKS_SELLER->value, $userId, $page),
            Mockery::type('array'),
            Mockery::type('closure')
        )
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getSellerFeedbacks')
        ->once()
        ->with($userId, $page)
        ->andReturn($paginator);

    $cachedService = new CachedFeedbackService($innerService);

    // Act
    $result = $cachedService->getSellerFeedbacks($userId, $page);

    // Assert
    expect($result)->toBe($paginator);
});
