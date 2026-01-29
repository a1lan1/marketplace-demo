<?php

declare(strict_types=1);

namespace App\Events\Payment;

use App\Contracts\LoggableEvent;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentMethodAdded implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PaymentMethod $paymentMethod,
        public User $user
    ) {}

    public function getPerformedOn(): ?Model
    {
        return $this->paymentMethod;
    }

    public function getCausedBy(): ?User
    {
        return $this->user;
    }

    public function getDescription(): string
    {
        return 'Payment method added';
    }

    public function getProperties(): array
    {
        return [];
    }
}
