<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Models\Transaction;
use Spatie\LaravelData\Data;

class TransferResultDTO extends Data
{
    public function __construct(
        public Transaction $senderTransaction,
        public Transaction $recipientTransaction,
    ) {}
}
