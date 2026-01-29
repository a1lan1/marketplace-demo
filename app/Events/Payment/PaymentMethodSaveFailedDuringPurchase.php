<?php

declare(strict_types=1);

namespace App\Events\Payment;

use App\Contracts\LoggableEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentMethodSaveFailedDuringPurchase implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $buyer,
        public string $errorMessage
    ) {}

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
        return 'Failed to save payment method during purchase';
    }

    public function getProperties(): array
    {
        return ['error' => $this->errorMessage];
    }
}
