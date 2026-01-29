<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

class MarketplaceStatsOverview extends BaseWidget
{
    #[Override]
    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', Product::query()->count())
                ->description('Number of products in the marketplace')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Orders', Order::query()->count())
                ->description('Total orders placed')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Pending Orders', Order::query()->where('status', OrderStatusEnum::PENDING)->count())
                ->description('Orders waiting for processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Total Users', User::query()->count())
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
