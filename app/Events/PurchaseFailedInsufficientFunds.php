<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseFailedInsufficientFunds implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public Money $amount
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->user;
    }

    public function getCausedBy(): ?User
    {
        return $this->user;
    }

    public function getDescription(): string
    {
        return 'Purchase failed: Insufficient funds';
    }

    public function getProperties(): array
    {
        return [
            'attempted_amount' => $this->amount->getAmount(),
            'current_balance' => $this->user->balance->getAmount(),
        ];
    }
}
