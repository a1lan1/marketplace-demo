<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Throwable;

interface BalanceServiceInterface
{
    /**
     * Deposit funds into the user's account.
     *
     * @throws Throwable
     */
    public function deposit(User $user, Money $amount, ?string $description = null): Transaction;

    /**
     * Withdraw funds from the user's account.
     *
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function withdraw(User $user, Money $amount, ?string $description = null): Transaction;

    /**
     * Check if the user has sufficient funds.
     */
    public function hasSufficientFunds(User $user, Money $amount): bool;
}
