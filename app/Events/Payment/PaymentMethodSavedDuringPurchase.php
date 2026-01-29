<?php

declare(strict_types=1);

namespace App\Events\Payment;

use App\Contracts\LoggableEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentMethodSavedDuringPurchase implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public User $buyer) {}

    public function getPerformedOn(): ?Model
    {
        return $this->buyer;
    }

    public function getCausedBy(): ?User
    {
        return $this->buyer;
    }

    public function getDescription(): string
    {
        return 'Payment method saved during purchase';
    }

    public function getProperties(): array
    {
        return [];
    }
}
