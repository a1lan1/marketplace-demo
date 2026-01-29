<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('user.name')
                    ->label('User')
                    ->disabled(),
                TextInput::make('amount')
                    ->formatStateUsing(fn (?int $state): float|int => $state ? $state / 100 : 0)
                    ->disabled(),
                TextInput::make('currency')
                    ->disabled(),
                Select::make('status')
                    ->disabled(),
                Select::make('provider')
                    ->disabled(),
                TextInput::make('transaction_id')
                    ->disabled(),
                DateTimePicker::make('created_at')
                    ->disabled(),
            ]);
    }
}
