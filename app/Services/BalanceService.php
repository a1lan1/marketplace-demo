<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Transactions\CreateTransactionAction;
use App\Contracts\BalanceServiceInterface;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class BalanceService implements BalanceServiceInterface
{
    public function __construct(private CreateTransactionAction $createTransactionAction) {}

    /**
     * @throws Throwable
     */
    public function deposit(User $user, Money $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description): Transaction {
            /** @var User $user */
            $user = User::query()->lockForUpdate()->find($user->id);

            $user->balance = $user->balance->add($amount);
            $user->save();

            return $this->createTransactionAction->execute(
                user: $user,
                amount: $amount,
                type: TransactionType::DEPOSIT,
                description: $description
            );
        });
    }

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function withdraw(User $user, Money $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description): Transaction {
            $user = User::query()->lockForUpdate()->find($user->id);

            if (! $this->hasSufficientFunds($user, $amount)) {
                throw new InsufficientFundsException;
            }

            $user->balance = $user->balance->subtract($amount);
            $user->save();

            return $this->createTransactionAction->execute(
                user: $user,
                amount: $amount,
                type: TransactionType::WITHDRAWAL,
                description: $description
            );
        });
    }

    public function hasSufficientFunds(User $user, Money $amount): bool
    {
        return $user->balance->greaterThanOrEqual($amount);
    }
}
