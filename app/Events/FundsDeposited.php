<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FundsDeposited implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public User $user,
        public Money $amount
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->transaction;
    }

    public function getCausedBy(): ?User
    {
        return $this->user;
    }

    public function getDescription(): string
    {
        return 'Funds deposited';
    }

    public function getProperties(): array
    {
        return [
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrency()->getCode(),
            'new_balance' => $this->user->refresh()->balance->getAmount(),
        ];
    }
}
