<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\Order\OrderStatusEnum;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('change_status')
                ->label('Change Status')
                ->icon('heroicon-o-pencil')
                ->schema([
                    Select::make('status')
                        ->options(OrderStatusEnum::class)
                        ->default(fn (Order $record): OrderStatusEnum => $record->status)
                        ->required(),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->status = $data['status'];
                    $record->save();

                    Notification::make()
                        ->success()
                        ->title('Order status updated.')
                        ->send();
                })
                ->visible(fn (): bool => Auth::user()?->isAdminOrManager()),
        ];
    }
}
