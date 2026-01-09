<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;

interface TransactionRepositoryInterface
{
    public function create(User $user, Money $amount, TransactionType $type, ?string $description = null): Transaction;
}
