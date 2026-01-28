<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutMethodDeleted implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PayoutMethod $payoutMethod,
        public User $user
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->payoutMethod;
    }

    public function getCausedBy(): ?User
    {
        return $this->user;
    }

    public function getDescription(): string
    {
        return 'Payout method deleted';
    }

    public function getProperties(): array
    {
        return [];
    }
}
