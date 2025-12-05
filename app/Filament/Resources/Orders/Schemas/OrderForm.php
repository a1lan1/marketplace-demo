<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('id')
                            ->label('Order ID')
                            ->disabled(),
                        TextInput::make('buyer.name')
                            ->label('Customer Name')
                            ->disabled(),
                        TextInput::make('total_amount.amount')
                            ->label('Total Amount')
                            ->hint('USD')
                            ->disabled(),
                        Select::make('status')
                            ->options(OrderStatusEnum::class)
                            ->required(),
                    ]),
            ]);
    }
}
