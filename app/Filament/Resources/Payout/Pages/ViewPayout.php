<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payout\Pages;

use App\Filament\Resources\Payout\PayoutResource;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewPayout extends ViewRecord
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
