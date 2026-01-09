<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->orderMock = $this->mock(Order::class);
    $this->messageRepositoryMock = $this->mock(MessageRepositoryInterface::class);
    $this->chatService = new ChatService($this->messageRepositoryMock);
});

test('get order messages returns a collection', function (): void {
    // Arrange
    $this->messageRepositoryMock
        ->shouldReceive('getForOrder')
        ->once()
        ->with($this->orderMock)
        ->andReturn(new Collection(['message1', 'message2']));

    // Act
    $result = $this->chatService->getOrderMessages($this->orderMock);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2);
});

test('get paginated messages returns a paginator', function (): void {
    // Arrange
    $paginatorMock = $this->mock(LengthAwarePaginator::class);
    $this->messageRepositoryMock
        ->shouldReceive('getPaginatedForOrder')
        ->once()
        ->with($this->orderMock, 50)
        ->andReturn($paginatorMock);

    // Act
    $result = $this->chatService->getPaginatedMessages($this->orderMock);

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});
