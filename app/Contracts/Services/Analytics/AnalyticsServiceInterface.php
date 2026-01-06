<?php

declare(strict_types=1);

namespace App\Contracts\Services\Analytics;

use Cknow\Money\Money;
use Illuminate\Support\Collection;

interface AnalyticsServiceInterface
{
    public function getTotalRevenueInUsd(): Money;

    public function getSalesByCurrency(): Collection;
}
