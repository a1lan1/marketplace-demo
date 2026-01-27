<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Models\PaymentMethod;
use App\Models\User;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Data;

class PaymentChargeDTO extends Data
{
    public function __construct(
        public User $user,
        public int $amount,
        public string $currency,
        public string $customerId,
        #[RequiredWithout('paymentMethodToken')]
        public ?PaymentMethod $paymentMethod = null,
        #[RequiredWithout('paymentMethod')]
        public ?string $paymentMethodToken = null,
        public ?string $idempotencyKey = null,
        public bool $saveCard = false,
        public ?string $returnUrl = null,
        public array $options = [],
    ) {}
}
