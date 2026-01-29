<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Transactions\CreateTransactionAction;
use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\Balance\DepositDTO;
use App\DTO\Balance\TransferDTO;
use App\DTO\Balance\WithdrawDTO;
use App\DTO\Payment\CreateTransactionDTO;
use App\DTO\Payment\TransferResultDTO;
use App\DTO\PurchaseOnBalanceDTO;
use App\Enums\Transaction\TransactionType;
use App\Events\Payment\FundsDeductedForPurchase;
use App\Events\Payment\FundsDeposited;
use App\Events\Payment\FundsTransferred;
use App\Events\Payment\FundsWithdrawn;
use App\Events\Payment\PurchaseFailedInsufficientFunds;
use App\Events\Payment\TransferFailedInsufficientFunds;
use App\Events\Payment\WithdrawalFailedInsufficientFunds;
use App\Exceptions\InsufficientFundsException;
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
    public function deposit(DepositDTO $dto): Transaction
    {
        return DB::transaction(function () use ($dto): Transaction {
            $user = User::query()->lockForUpdate()->find($dto->user->id);

            $user->update(['balance' => $user->balance->add($dto->amount)]);

            $transaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $dto->user,
                    amount: $dto->amount,
                    type: TransactionType::DEPOSIT,
                    orderId: $dto->order?->id,
                    description: $dto->description
                )
            );

            event(new FundsDeposited($transaction, $dto->user, $dto->amount));

            return $transaction;
        });
    }

    /**
     * @throws Throwable
     * @throws InsufficientFundsException
     */
    public function withdraw(WithdrawDTO $dto): Transaction
    {
        return DB::transaction(function () use ($dto): Transaction {
            $user = User::query()->lockForUpdate()->find($dto->user->id);

            if (! $this->hasSufficientFunds($user, $dto->amount)) {
                event(new WithdrawalFailedInsufficientFunds($user, $dto->amount));
                throw new InsufficientFundsException;
            }

            $user->update(['balance' => $user->balance->subtract($dto->amount)]);

            $transaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $dto->user,
                    amount: $dto->amount,
                    type: TransactionType::WITHDRAWAL,
                    description: $dto->description
                )
            );

            $gateway = $this->paymentGatewayFactory->make($dto->payoutMethod->provider);
            $payoutResult = $gateway->createPayout($dto->payoutMethod, $dto->amount);

            $transaction->update([
                'provider_transaction_id' => $payoutResult->payoutId,
                'metadata' => [
                    'payout_status' => $payoutResult->status,
                    'arrival_date' => $payoutResult->arrivalDate,
                ],
            ]);

            event(new FundsWithdrawn($transaction, $user, $dto->amount, $payoutResult->payoutId));

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
    public function transfer(TransferDTO $dto): TransferResultDTO
    {
        return DB::transaction(function () use ($dto): TransferResultDTO {
            // Lock users in consistent order to prevent deadlocks
            $firstId = min($dto->sender->id, $dto->recipient->id);
            $secondId = max($dto->sender->id, $dto->recipient->id);

            $users = User::query()->lockForUpdate()->findMany([$firstId, $secondId]);
            $sender = $users->find($dto->sender->id);
            $recipient = $users->find($dto->recipient->id);

            if (! $this->hasSufficientFunds($sender, $dto->amount)) {
                event(new TransferFailedInsufficientFunds($sender, $recipient, $dto->amount));
                throw new InsufficientFundsException;
            }

            $sender->update(['balance' => $sender->balance->subtract($dto->amount)]);

            $recipient->update(['balance' => $recipient->balance->add($dto->amount)]);

            $senderTransaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $sender,
                    amount: $dto->amount,
                    type: TransactionType::TRANSFER,
                    description: $dto->description ? sprintf('Transfer to %s: %s', $recipient->name, $dto->description) : 'Transfer to '.$recipient->name
                )
            );

            $recipientTransaction = $this->createTransactionAction->execute(
                new CreateTransactionDTO(
                    user: $recipient,
                    amount: $dto->amount,
                    type: TransactionType::TRANSFER,
                    description: $dto->description ? sprintf('Transfer from %s: %s', $sender->name, $dto->description) : 'Transfer from '.$sender->name
                )
            );

            event(new FundsTransferred($senderTransaction, $sender, $recipient, $dto->amount));

            return new TransferResultDTO(
                senderTransaction: $senderTransaction,
                recipientTransaction: $recipientTransaction,
            );
        });
    }

    public function hasSufficientFunds(User $user, Money $amount): bool
    {
        return $user->balance->greaterThanOrEqual($amount);
    }
}
