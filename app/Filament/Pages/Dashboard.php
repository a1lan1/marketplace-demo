<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\MarketplaceStatsOverview;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\ProductsChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Override;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            MarketplaceStatsOverview::class,
        ];
    }

    #[Override]
    public function getWidgets(): array
    {
        return [
            OrdersChart::class,
            ProductsChart::class,
        ];
    }
}
