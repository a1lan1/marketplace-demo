<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Feedback;

use App\Contracts\Repositories\FeedbackRepositoryInterface;
use App\Contracts\Services\Feedback\FeedbackableMapInterface;
use App\Models\Product;
use App\Services\Feedback\FeedbackService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;

beforeEach(function (): void {
    $this->feedbackableMapMock = Mockery::mock(FeedbackableMapInterface::class);
    $this->feedbackRepositoryMock = Mockery::mock(FeedbackRepositoryInterface::class);
    $this->feedbackService = new FeedbackService(
        $this->feedbackableMapMock,
        $this->feedbackRepositoryMock
    );
});

test('get feedbacks for target resolves type and calls repository', function (): void {
    // Arrange
    $type = 'product';
    $id = 123;
    $page = 2;
    $modelClass = Product::class;
    $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

    $this->feedbackableMapMock
        ->shouldReceive('get')
        ->once()
        ->with($type)
        ->andReturn($modelClass);

    $this->feedbackRepositoryMock
        ->shouldReceive('getForEntity')
        ->once()
        ->with($modelClass, $id, 15, $page)
        ->andReturn($paginatorMock);

    // Act
    $result = $this->feedbackService->getFeedbacksForTarget($type, $id, $page);

    // Assert
    expect($result)->toBe($paginatorMock);
});

test('get seller feedbacks calls repository', function (): void {
    // Arrange
    $userId = 456;
    $page = 1;
    $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

    $this->feedbackRepositoryMock
        ->shouldReceive('getForUser')
        ->once()
        ->with($userId, 15, $page)
        ->andReturn($paginatorMock);

    // Act
    $result = $this->feedbackService->getSellerFeedbacks($userId, $page);

    // Assert
    expect($result)->toBe($paginatorMock);
});
