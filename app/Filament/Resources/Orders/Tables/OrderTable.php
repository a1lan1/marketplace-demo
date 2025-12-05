<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable(),
                TextColumn::make('buyer.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatusEnum::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('change_status')
                    ->label('Change Status')
                    ->icon('heroicon-o-pencil')
                    ->schema([
                        Select::make('status')
                            ->options(OrderStatusEnum::class)
                            ->default(fn (Order $record) => $record->status)
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->status = $data['status'];
                        $record->save();
                    })
                    ->visible(fn (Order $record) => Auth::user()?->isAdminOrManager()),
            ]);
    }
}
