<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payout\Pages;

use App\Filament\Resources\Payout\PayoutResource;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
