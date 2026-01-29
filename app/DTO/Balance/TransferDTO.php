<?php

declare(strict_types=1);

namespace App\DTO\Balance;

use App\Models\User;
use Cknow\Money\Money;
use Spatie\LaravelData\Data;

class TransferDTO extends Data
{
    public function __construct(
        public User $sender,
        public User $recipient,
        public Money $amount,
        public ?string $description = null,
    ) {}
}
