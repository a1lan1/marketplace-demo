<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\DTO\Payment\CreateTransactionDTO;
use App\Models\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(CreateTransactionDTO $dto): Transaction
    {
        return Transaction::create($dto->forModel());
    }
}
