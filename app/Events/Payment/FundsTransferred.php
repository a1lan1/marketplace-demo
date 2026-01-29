<?php

declare(strict_types=1);

namespace App\Events\Payment;

use App\Contracts\LoggableEvent;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FundsTransferred implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Transaction $senderTransaction,
        public User $sender,
        public User $recipient,
        public Money $amount
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->senderTransaction;
    }

    public function getCausedBy(): ?User
    {
        return $this->sender;
    }

    public function getDescription(): string
    {
        return 'Funds transferred';
    }

    public function getProperties(): array
    {
        return [
            'recipient_id' => $this->recipient->id,
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrency()->getCode(),
        ];
    }
}
