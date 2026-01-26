<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\DTO\Payment\CreateTransactionDTO;
use App\Models\Transaction;

class CreateTransactionAction
{
    public function __construct(protected TransactionRepositoryInterface $transactionRepository) {}

    public function execute(CreateTransactionDTO $dto): Transaction
    {
        return $this->transactionRepository->create($dto);
    }
}
