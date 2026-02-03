<?php

declare(strict_types=1);

namespace App\Events\Payment;

use App\Contracts\LoggableEvent;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FundsTransferred implements LoggableEvent, ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public User $sender,
        public User $recipient,
        public Money $amount
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->transaction;
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
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrency()->getCode(),
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'new_sender_balance' => $this->sender->refresh()->balance->getAmount(),
        ];
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->sender->id),
            new PrivateChannel('App.Models.User.'.$this->recipient->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'funds.transferred';
    }
}
