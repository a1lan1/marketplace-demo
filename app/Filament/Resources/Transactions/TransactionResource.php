<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Infolists\TransactionInfolist;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\ViewTransaction;
use App\Filament\Resources\Transactions\Tables\TransactionTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $slug = 'transactions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return TransactionInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return TransactionTable::configure($table);
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
            'index' => ListTransactions::route('/'),
            'view' => ViewTransaction::route('/{record}'),
        ];
    }
}
