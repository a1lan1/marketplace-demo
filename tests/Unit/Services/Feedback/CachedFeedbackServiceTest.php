<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\FeedbackServiceInterface;
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

    Cache::shouldReceive('tags')->with(['feedbacks'])->andReturnSelf();
    Cache::shouldReceive('remember')
        ->once()
        ->with(sprintf('feedbacks_target_%s_%d_page_1', $type, $id), 3600, Mockery::type('closure'))
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getFeedbacksForTarget')
        ->once()
        ->with($type, $id, 1)
        ->andReturn($paginator);

    $cachedService = new CachedFeedbackService($innerService);

    // Act
    $result = $cachedService->getFeedbacksForTarget($type, $id);

    // Assert
    expect($result)->toBe($paginator);
});

test('get seller feedbacks returns cached result', function (): void {
    // Arrange
    $innerService = Mockery::mock(FeedbackServiceInterface::class);
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $userId = 1;

    Cache::shouldReceive('tags')->with(['feedbacks'])->andReturnSelf();
    Cache::shouldReceive('remember')
        ->once()
        ->with(sprintf('feedbacks_seller_%d_page_1', $userId), 3600, Mockery::type('closure'))
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getSellerFeedbacks')
        ->once()
        ->with($userId, 1)
        ->andReturn($paginator);

    $cachedService = new CachedFeedbackService($innerService);

    // Act
    $result = $cachedService->getSellerFeedbacks($userId);

    // Assert
    expect($result)->toBe($paginator);
});
