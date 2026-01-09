<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Geo;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\DTO\Geo\ReviewFilterData;
use App\Models\User;
use App\Services\Geo\ReviewService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;

beforeEach(function (): void {
    $this->reviewRepositoryMock = Mockery::mock(ReviewRepositoryInterface::class);
    $this->reviewService = new ReviewService($this->reviewRepositoryMock);
});

test('get reviews for user calls repository with filters', function (): void {
    // Arrange
    $user = User::factory()->make();
    $filters = new ReviewFilterData(
        locationId: 1
    );
    $page = 1;
    $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

    $this->reviewRepositoryMock
        ->shouldReceive('getForUserWithFilters')
        ->once()
        ->with($user, $filters, 15, $page)
        ->andReturn($paginatorMock);

    // Act
    $result = $this->reviewService->getReviewsForUser($user, $filters, $page);

    // Assert
    expect($result)->toBe($paginatorMock);
});
