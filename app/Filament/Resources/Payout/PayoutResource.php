<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payout;

use App\Enums\Transaction\TransactionType;
use App\Filament\Resources\Payout\Infolists\PayoutInfolist;
use App\Filament\Resources\Payout\Pages\ListPayouts;
use App\Filament\Resources\Payout\Pages\ViewPayout;
use App\Filament\Resources\Payout\Tables\PayoutTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class PayoutResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $slug = 'payouts';

    protected static ?string $navigationLabel = 'Payouts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return PayoutInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return PayoutTable::configure($table);
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', TransactionType::DEPOSIT)
            ->whereNotNull('order_id');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
            'view' => ViewPayout::route('/{record}'),
        ];
    }
}
