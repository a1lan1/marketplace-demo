<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferFailedInsufficientFunds implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $sender,
        public User $recipient,
        public Money $amount
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->sender;
    }

    public function getCausedBy(): ?User
    {
        return $this->sender;
    }

    public function getDescription(): string
    {
        return 'Transfer failed: Insufficient funds';
    }

    public function getProperties(): array
    {
        return [
            'recipient_id' => $this->recipient->id,
            'attempted_amount' => $this->amount->getAmount(),
            'current_balance' => $this->sender->balance->getAmount(),
        ];
    }
}
