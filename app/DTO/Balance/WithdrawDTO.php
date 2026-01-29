<?php

declare(strict_types=1);

namespace App\DTO\Balance;

use App\Models\PayoutMethod;
use App\Models\User;
use Cknow\Money\Money;
use Spatie\LaravelData\Data;

class WithdrawDTO extends Data
{
    public function __construct(
        public User $user,
        public Money $amount,
        public PayoutMethod $payoutMethod,
        public ?string $description = null,
    ) {}
}
