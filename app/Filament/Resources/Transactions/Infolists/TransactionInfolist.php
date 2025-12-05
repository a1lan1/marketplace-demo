<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Transaction Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Transaction ID'),
                        TextEntry::make('type')
                            ->badge(),
                        TextEntry::make('amount')
                            ->money('USD'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ]),

                Section::make('Related Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('User'),
                        TextEntry::make('user.email')
                            ->label('User Email'),
                        TextEntry::make('order_id')
                            ->label('Order ID')
                            ->placeholder('No related order'),
                        TextEntry::make('order.status')
                            ->label('Order Status')
                            ->badge()
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record->order_id !== null),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record): bool => $record->description !== null),
            ]);
    }
}
