<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BalanceServiceInterface;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Facades\DB;
use Throwable;

class BalanceService implements BalanceServiceInterface
{
    /**
     * @throws Throwable
     */
    public function deposit(User $user, Money $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description) {
            $user->balance = $user->balance->add($amount);
            $user->save();

            return Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => TransactionType::DEPOSIT,
                'description' => $description,
            ]);
        });
    }

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function withdraw(User $user, Money $amount, ?string $description = null): Transaction
    {
        if (! $this->hasSufficientFunds($user, $amount)) {
            throw new InsufficientFundsException;
        }

        return DB::transaction(function () use ($user, $amount, $description) {
            $user->balance = $user->balance->subtract($amount);
            $user->save();

            return Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount->negative(),
                'type' => TransactionType::WITHDRAWAL,
                'description' => $description,
            ]);
        });
    }

    public function hasSufficientFunds(User $user, Money $amount): bool
    {
        return $user->balance->greaterThanOrEqual($amount);
    }
}
