<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Enums\PaymentProviderEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\User;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProcessPaymentDTO extends Data
{
    public function __construct(
        public User $user,
        public int $amount,
        public string $currency,
        public string $paymentMethodId,
        public bool $saveCard,
        public PaymentProviderEnum $provider,
        public ?string $idempotencyKey = null,
        public ?PaymentStatusEnum $status = null,
        public ?string $resolvedPaymentMethodId = null,
        public array $metadata = [],
    ) {}

    public function forModel(): array
    {
        return [
            'user_id' => $this->user->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'provider' => $this->provider,
            'status' => $this->status ?? PaymentStatusEnum::PENDING,
            'payment_method_id' => $this->resolvedPaymentMethodId,
            'idempotency_key' => $this->idempotencyKey,
            'metadata' => $this->metadata,
        ];
    }
}
