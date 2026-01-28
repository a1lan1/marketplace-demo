<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Enums\PaymentStatusEnum;
use Spatie\LaravelData\Data;

class ProcessPaymentResultDTO extends Data
{
    public function __construct(
        public string $paymentId,
        public PaymentStatusEnum $status,
        public ?string $message = null,
    ) {}
}
