<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\Payment\CreateTransactionDTO;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    public function create(CreateTransactionDTO $dto): Transaction;

    /**
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator;
}
