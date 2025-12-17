<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;

class CreateTransactionAction
{
    public function execute(User $user, Money $amount, TransactionType $type, ?string $description = null): Transaction
    {
        return Transaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
        ]);
    }
}
