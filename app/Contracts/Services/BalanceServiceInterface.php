<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTO\Balance\DepositDTO;
use App\DTO\Balance\TransferDTO;
use App\DTO\Balance\WithdrawDTO;
use App\DTO\Payment\TransferResultDTO;
use App\DTO\PurchaseOnBalanceDTO;
use App\Exceptions\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Throwable;

interface BalanceServiceInterface
{
    /**
     * @throws Throwable
     */
    public function deposit(DepositDTO $dto): Transaction;

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function withdraw(WithdrawDTO $dto): Transaction;

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function purchase(PurchaseOnBalanceDTO $dto): Transaction;

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function transfer(TransferDTO $dto): TransferResultDTO;

    public function hasSufficientFunds(User $user, Money $amount): bool;
}
