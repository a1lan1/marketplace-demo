<?php

declare(strict_types=1);

namespace App\DTO\Balance;

use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Spatie\LaravelData\Data;

class DepositDTO extends Data
{
    public function __construct(
        public User $user,
        public Money $amount,
        public ?Order $order = null,
        public ?string $description = null,
    ) {}
}
