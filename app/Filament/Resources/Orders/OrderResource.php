<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Infolists\OrderInfolist;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrderTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return OrderTable::configure($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
