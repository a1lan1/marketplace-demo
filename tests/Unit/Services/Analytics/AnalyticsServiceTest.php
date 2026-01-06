<?php

declare(strict_types=1);

use App\Contracts\Services\CurrencyServiceInterface;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\Analytics\AnalyticsService;
use Cknow\Money\Money;

beforeEach(function (): void {
    $this->currencyService = $this->mock(CurrencyServiceInterface::class);
    $this->analyticsService = new AnalyticsService($this->currencyService);
});

it('returns correct total revenue in usd', function (): void {
    Order::factory()->create(['status' => OrderStatusEnum::COMPLETED, 'total_amount' => 10000]);
    Order::factory()->create(['status' => OrderStatusEnum::COMPLETED, 'total_amount' => 5000]);
    Order::factory()->create(['status' => OrderStatusEnum::PENDING, 'total_amount' => 2000]);

    expect($this->analyticsService->getTotalRevenueInUsd())->toEqual(Money::USD(15000));
});

it('returns correct sales by currency', function (): void {
    Order::factory()->count(3)->create(['total_amount' => 10000]);

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
    expect($this->analyticsService->getSalesByCurrency())->toBeEmpty();
});
