<?php

declare(strict_types=1);

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\CurrencyServiceInterface;
use App\DTO\SalesStatsDTO;
use App\Enums\OrderStatusEnum;
use App\Services\Analytics\AnalyticsService;
use Cknow\Money\Money;

beforeEach(function (): void {
    $this->currencyService = $this->mock(CurrencyServiceInterface::class);
    $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
    $this->analyticsService = new AnalyticsService($this->currencyService, $this->orderRepository);
});

it('returns correct total revenue in usd', function (): void {
    $this->orderRepository
        ->shouldReceive('sumTotalAmountByStatus')
        ->once()
        ->with(OrderStatusEnum::COMPLETED)
        ->andReturn(15000);

    expect($this->analyticsService->getTotalRevenueInUsd())->toEqual(Money::USD(15000));
});

it('returns correct sales by currency', function (): void {
    $stats = new SalesStatsDTO(count: 3, totalCents: 30000);

    $this->orderRepository
        ->shouldReceive('getSalesStatsByCurrency')
        ->once()
        ->andReturn($stats);

    $expected = collect([
        [
            'currency' => 'USD',
            'count' => 3,
            'total' => Money::USD(30000)->format(),
        ],
    ]);

    expect($this->analyticsService->getSalesByCurrency())->toEqual($expected);
});

it('returns empty collection when no orders for sales by currency', function (): void {
    $this->orderRepository
        ->shouldReceive('getSalesStatsByCurrency')
        ->once()
        ->andReturn(null);

    expect($this->analyticsService->getSalesByCurrency())->toBeEmpty();
});
