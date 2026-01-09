<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;

class CreateTransactionAction
{
    public function __construct(protected TransactionRepositoryInterface $transactionRepository) {}

    public function execute(User $user, Money $amount, TransactionType $type, ?string $description = null): Transaction
    {
        return $this->transactionRepository->create($user, $amount, $type, $description);
    }
}
