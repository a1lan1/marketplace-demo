<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalFailedInsufficientFunds
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public Money $amount
    ) {}
}
