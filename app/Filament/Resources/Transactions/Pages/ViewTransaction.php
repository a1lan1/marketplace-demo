<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
