<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\Analytics\AnalyticsServiceInterface;
use App\Contracts\Services\CurrencyServiceInterface;
use App\Enums\OrderStatusEnum;
use Cknow\Money\Money;
use Illuminate\Support\Collection;

class AnalyticsService implements AnalyticsServiceInterface
{
    public function __construct(
        protected CurrencyServiceInterface $currencyService,
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function getTotalRevenueInUsd(): Money
    {
        $totalCents = $this->orderRepository->sumTotalAmountByStatus(OrderStatusEnum::COMPLETED);

        return Money::USD($totalCents);
    }

    public function getSalesByCurrency(): Collection
    {
        $stats = $this->orderRepository->getSalesStatsByCurrency();

        return collect([
            [
                'currency' => $stats->currency,
                'count' => $stats->count,
                'total' => Money::USD($stats->totalCents)->format(),
            ],
        ]);
    }
}
