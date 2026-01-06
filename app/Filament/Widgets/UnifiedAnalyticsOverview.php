<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Contracts\Services\Analytics\AnalyticsServiceInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

class UnifiedAnalyticsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    #[Override]
    protected function getStats(): array
    {
        $analyticsService = app(AnalyticsServiceInterface::class);
        $revenue = $analyticsService->getTotalRevenueInUsd();
        $salesByCurrency = $analyticsService->getSalesByCurrency();

        $stats = [
            Stat::make('Total Revenue (Normalized)', $revenue->format())
                ->description('Total revenue converted to USD')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];

        foreach ($salesByCurrency as $row) {
            $stats[] = Stat::make('Sales in '.$row['currency'], $row['total'])
                ->description($row['count'].' orders')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info');
        }

        return $stats;
    }
}
