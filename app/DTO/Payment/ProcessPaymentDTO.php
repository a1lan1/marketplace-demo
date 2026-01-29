<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Enums\PaymentProviderEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProcessPaymentDTO extends Data
{
    public function __construct(
        public User $user,
        public int $amount,
        public string $currency,
        public bool $saveCard,
        public PaymentProviderEnum $provider,
        #[RequiredWithout('paymentMethodToken')]
        #[Uuid]
        public ?string $paymentMethodId = null,
        #[RequiredWithout('paymentMethodId')]
        public ?string $paymentMethodToken = null,
        public ?string $idempotencyKey = null,
        public ?PaymentStatusEnum $status = null,
        public ?string $resolvedPaymentMethodId = null,
        public array $metadata = [],
    ) {}

    public static function make(array $data): self
    {
        $paymentMethodInput = $data['paymentMethodId'] ?? $data['payment_method_id'] ?? null;

        if ($paymentMethodInput) {
            $isUuid = Str::isUuid($paymentMethodInput);

            if ($isUuid) {
                $data['paymentMethodId'] = $paymentMethodInput;
                // Ensure token is null if we found a UUID
                unset($data['paymentMethodToken']);
            } else {
                $data['paymentMethodToken'] = $paymentMethodInput;
                // Ensure ID is null if it's a token
                unset($data['paymentMethodId']);
            }
        }

        return self::from($data);
    }

    public function getMoney(): Money
    {
        return new Money($this->amount, $this->currency);
    }

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
