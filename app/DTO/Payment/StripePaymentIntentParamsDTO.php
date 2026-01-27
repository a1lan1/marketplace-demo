<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Models\PaymentMethod;
use Spatie\LaravelData\Data;

class StripePaymentIntentParamsDTO extends Data
{
    public function __construct(
        public int $amount,
        public string $currency,
        public string $customerId,
        public bool $confirm = true,
        public ?string $returnUrl = null,
        public ?string $paymentMethod = null,
        public ?string $setupFutureUsage = null,
    ) {}

    public static function fromChargeDTO(PaymentChargeDTO $dto): self
    {
        $paymentMethod = null;
        if ($dto->paymentMethod instanceof PaymentMethod) {
            $paymentMethod = $dto->paymentMethod->provider_id;
        } elseif ($dto->paymentMethodToken) {
            $paymentMethod = $dto->paymentMethodToken;
        }

        return new self(
            amount: $dto->amount,
            currency: $dto->currency,
            customerId: $dto->customerId,
            returnUrl: $dto->returnUrl ?? route('home'),
            paymentMethod: $paymentMethod,
            setupFutureUsage: $dto->saveCard ? 'off_session' : null,
        );
    }

    public function toStripeArray(): array
    {
        $data = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer' => $this->customerId,
            'confirm' => $this->confirm,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ];

        if ($this->returnUrl) {
            $data['return_url'] = $this->returnUrl;
        }

        if ($this->paymentMethod) {
            $data['payment_method'] = $this->paymentMethod;
        }

        if ($this->setupFutureUsage) {
            $data['setup_future_usage'] = $this->setupFutureUsage;
        }

        return $data;
    }
}
