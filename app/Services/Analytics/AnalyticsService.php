<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Contracts\Services\Analytics\AnalyticsServiceInterface;
use App\Contracts\Services\CurrencyServiceInterface;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Cknow\Money\Money;
use Illuminate\Support\Collection;

class AnalyticsService implements AnalyticsServiceInterface
{
    public function __construct(protected CurrencyServiceInterface $currencyService) {}

    public function getTotalRevenueInUsd(): Money
    {
        $totalCents = Order::query()
            ->where('status', OrderStatusEnum::COMPLETED)
            ->sum('total_amount');

        return Money::USD((int) $totalCents);
    }

    public function getSalesByCurrency(): Collection
    {
        $stats = Order::query()
            ->selectRaw('count(*) as count, sum(total_amount) as total')
            ->toBase()
            ->first();

        if (! $stats || ! $stats->count) {
            return collect();
        }

        return collect([
            [
                'currency' => 'USD',
                'count' => $stats->count,
                'total' => Money::USD((int) $stats->total)->format(),
            ],
        ]);
    }
}
