<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payout\Infolists;

use App\Filament\Resources\Transactions\Infolists\TransactionInfolist;
use Filament\Schemas\Schema;

class PayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return TransactionInfolist::configure($schema);
    }
}
