<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\Payment\CreateTransactionDTO;
use App\Models\Transaction;

interface TransactionRepositoryInterface
{
    public function create(CreateTransactionDTO $dto): Transaction;
}
