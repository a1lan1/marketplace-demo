<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Enums\Payment\PaymentStatusEnum;
use App\Models\Payment;
use Spatie\LaravelData\Data;

class PaymentUpdateDTO extends Data
{
    public function __construct(
        public PaymentStatusEnum $status,
        public ?string $transactionId = null,
        public array $metadata = [],
    ) {}

    public function getUpdatePayload(Payment $payment): array
    {
        $data = ['status' => $this->status];

        if ($this->transactionId) {
            $data['transaction_id'] = $this->transactionId;
        }

        if ($this->metadata !== []) {
            $data['metadata'] = array_merge($payment->metadata ?? [], $this->metadata);
        }

        return $data;
    }
}
