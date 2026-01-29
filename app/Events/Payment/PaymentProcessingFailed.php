<?php

declare(strict_types=1);

namespace App\Events\Payment;

use App\Contracts\LoggableEvent;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessingFailed implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
        public User $user,
        public string $errorMessage
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->payment;
    }

    public function getCausedBy(): ?User
    {
        return $this->user;
    }

    public function getDescription(): string
    {
        return 'Payment processing failed';
    }

    public function getProperties(): array
    {
        return ['error' => $this->errorMessage];
    }
}
