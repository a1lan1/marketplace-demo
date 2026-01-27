<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FundsTransferred
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Transaction $senderTransaction,
        public User $sender,
        public User $recipient,
        public Money $amount
    ) {}
}
