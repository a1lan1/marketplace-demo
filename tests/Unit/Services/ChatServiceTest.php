<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

beforeEach(function (): void {
    $this->orderMock = $this->mock(Order::class);
    $this->chatService = new ChatService;
});

test('get order messages returns a collection', function (): void {
    // Arrange
    $relationMock = $this->mock(HasMany::class, function (MockInterface $mock): void {
        $mock->shouldReceive('select')->once()->with(Mockery::type('array'))->andReturnSelf();
        $mock->shouldReceive('with')->once()->with(Mockery::type('array'))->andReturnSelf();
        $mock->shouldReceive('get')->once()->andReturn(new Collection(['message1', 'message2']));
    });

    $this->orderMock->shouldReceive('messages')->once()->andReturn($relationMock);

    // Act
    $result = $this->chatService->getOrderMessages($this->orderMock);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2);
});

test('get paginated messages returns a paginator', function (): void {
    // Arrange
    $paginatorMock = $this->mock(LengthAwarePaginator::class);
    $relationMock = $this->mock(HasMany::class, function (MockInterface $mock) use ($paginatorMock): void {
        $mock->shouldReceive('select')->once()->with(Mockery::type('array'))->andReturnSelf();
        $mock->shouldReceive('with')->once()->with(Mockery::type('array'))->andReturnSelf();
        $mock->shouldReceive('latest')->once()->andReturnSelf();
        $mock->shouldReceive('paginate')->with(50)->once()->andReturn($paginatorMock);
    });

    $this->orderMock->shouldReceive('messages')->once()->andReturn($relationMock);

    // Act
    $result = $this->chatService->getPaginatedMessages($this->orderMock);

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});
