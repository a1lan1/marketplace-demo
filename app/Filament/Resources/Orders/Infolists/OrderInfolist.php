<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Infolists;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('total_amount')
                            ->money('USD'),
                        TextEntry::make('buyer.name')
                            ->label('Customer'),
                        TextEntry::make('buyer.email')
                            ->label('Customer Email'),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('products')
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('pivot.quantity')
                                    ->label('Quantity'),
                                TextEntry::make('pivot.price')
                                    ->label('Price at time of order')
                                    ->money('USD'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
