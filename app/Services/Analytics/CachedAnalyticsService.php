<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Contracts\Services\Analytics\AnalyticsServiceInterface;
use App\Enums\CacheKeyEnum;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

readonly class CachedAnalyticsService implements AnalyticsServiceInterface
{
    public function __construct(private AnalyticsServiceInterface $service) {}

    public function getTotalRevenueInUsd(): Money
    {
        return Cache::tags(['analytics'])->flexible(
            CacheKeyEnum::ANALYTICS_TOTAL_REVENUE->value,
            [Date::now()->addMinute(), Date::now()->addMinutes(10)],
            fn (): Money => $this->service->getTotalRevenueInUsd()
        );
    }

    public function getSalesByCurrency(): Collection
    {
        return Cache::tags(['analytics'])->flexible(
            CacheKeyEnum::ANALYTICS_SALES_BY_CURRENCY->value,
            [Date::now()->addMinute(), Date::now()->addMinutes(10)],
            fn (): Collection => $this->service->getSalesByCurrency()
        );
    }
}
