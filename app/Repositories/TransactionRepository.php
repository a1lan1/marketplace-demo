<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\DTO\Payment\CreateTransactionDTO;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(CreateTransactionDTO $dto): Transaction
    {
        return Transaction::create($dto->forModel());
    }

    /**
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Transaction::query()
            ->select([
                'id',
                'order_id',
                'amount',
                'type',
                'description',
                'created_at',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }
}
