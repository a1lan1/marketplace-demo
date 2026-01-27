<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FundsWithdrawnFromBalance
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $buyer,
        public Order $order,
        public Money $totalAmount
    ) {}
}
