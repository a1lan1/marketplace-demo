<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\PurchaseOnBalanceDTO;
use App\Exceptions\InsufficientFundsException;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Throwable;

interface BalanceServiceInterface
{
    public function deposit(User $user, Money $amount, ?string $description = null): Transaction;

    /**
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function withdraw(User $user, Money $amount, PayoutMethod $payoutMethod, ?string $description = null): Transaction;

    /**
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function purchase(PurchaseOnBalanceDTO $dto): Transaction;

    /**
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function transfer(User $sender, User $recipient, Money $amount, ?string $description = null): array;

    public function hasSufficientFunds(User $user, Money $amount): bool;
}
