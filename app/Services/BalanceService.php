<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Transactions\CreateTransactionAction;
use App\Contracts\BalanceServiceInterface;
use App\DTO\Payment\CreateTransactionDTO;
use App\DTO\PurchaseOnBalanceDTO;
use App\Enums\TransactionType;
use App\Events\FundsDeductedForPurchase;
use App\Events\FundsDeposited;
use App\Events\FundsTransferred;
use App\Events\FundsWithdrawn;
use App\Events\PurchaseFailedInsufficientFunds;
use App\Events\TransferFailedInsufficientFunds;
use App\Events\WithdrawalFailedInsufficientFunds;
use App\Exceptions\InsufficientFundsException;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payment\PaymentGatewayFactory;
use Cknow\Money\Money;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class BalanceService implements BalanceServiceInterface
{
    public function __construct(
        private CreateTransactionAction $createTransactionAction,
        private PaymentGatewayFactory $paymentGatewayFactory
    ) {}

    /**
     * @throws Throwable
     */
    public function deposit(User $user, Money $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description): Transaction {
            $user = User::query()->lockForUpdate()->find($user->id);

            $user->update(['balance' => $user->balance->add($amount)]);

            $transaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $user,
                    amount: $amount,
                    type: TransactionType::DEPOSIT,
                    description: $description
                )
            );

            event(new FundsDeposited($transaction, $user, $amount));

            return $transaction;
        });
    }

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function withdraw(User $user, Money $amount, PayoutMethod $payoutMethod, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $payoutMethod, $description): Transaction {
            $user = User::query()->lockForUpdate()->find($user->id);

            if (! $this->hasSufficientFunds($user, $amount)) {
                event(new WithdrawalFailedInsufficientFunds($user, $amount));
                throw new InsufficientFundsException;
            }

            $user->update(['balance' => $user->balance->subtract($amount)]);

            $transaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $user,
                    amount: $amount,
                    type: TransactionType::WITHDRAWAL,
                    description: $description
                )
            );

            $gateway = $this->paymentGatewayFactory->make($payoutMethod->provider);
            $payoutResult = $gateway->createPayout($payoutMethod, $amount);

            $transaction->update([
                'provider_transaction_id' => $payoutResult->payoutId,
                'metadata' => [
                    'payout_status' => $payoutResult->status,
                    'arrival_date' => $payoutResult->arrivalDate,
                ],
            ]);

            event(new FundsWithdrawn($transaction, $user, $amount, $payoutResult->payoutId));

            return $transaction;
        });
    }

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function purchase(PurchaseOnBalanceDTO $dto): Transaction
    {
        return DB::transaction(function () use ($dto): Transaction {
            $user = User::query()->lockForUpdate()->find($dto->user->id);

            if (! $this->hasSufficientFunds($user, $dto->amount)) {
                event(new PurchaseFailedInsufficientFunds($user, $dto->amount));
                throw new InsufficientFundsException;
            }

            $user->update(['balance' => $user->balance->subtract($dto->amount)]);

            $transaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $user,
                    amount: $dto->amount,
                    type: TransactionType::PURCHASE,
                    orderId: $dto->order?->id,
                    description: $dto->description
                )
            );

            event(new FundsDeductedForPurchase($transaction, $user, $dto->amount));

            return $transaction;
        });
    }

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function transfer(User $sender, User $recipient, Money $amount, ?string $description = null): array
    {
        return DB::transaction(function () use ($sender, $recipient, $amount, $description): array {
            // Lock users in consistent order to prevent deadlocks
            $firstId = min($sender->id, $recipient->id);
            $secondId = max($sender->id, $recipient->id);

            $users = User::query()->lockForUpdate()->findMany([$firstId, $secondId]);
            $sender = $users->find($sender->id);
            $recipient = $users->find($recipient->id);

            if (! $this->hasSufficientFunds($sender, $amount)) {
                event(new TransferFailedInsufficientFunds($sender, $recipient, $amount));
                throw new InsufficientFundsException;
            }

            $sender->update(['balance' => $sender->balance->subtract($amount)]);

            $recipient->update(['balance' => $recipient->balance->add($amount)]);

            $senderTransaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $sender,
                    amount: $amount,
                    type: TransactionType::TRANSFER,
                    description: $description ? sprintf('Transfer to %s: %s', $recipient->name, $description) : 'Transfer to '.$recipient->name
                )
            );

            $recipientTransaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $recipient,
                    amount: $amount,
                    type: TransactionType::TRANSFER,
                    description: $description ? sprintf('Transfer from %s: %s', $sender->name, $description) : 'Transfer from '.$sender->name
                )
            );

            event(new FundsTransferred($senderTransaction, $sender, $recipient, $amount));

            return [
                'senderTransaction' => $senderTransaction,
                'recipientTransaction' => $recipientTransaction,
            ];
        });
    }

    public function hasSufficientFunds(User $user, Money $amount): bool
    {
        return $user->balance->greaterThanOrEqual($amount);
    }
}
