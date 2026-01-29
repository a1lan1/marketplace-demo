<?php

declare(strict_types=1);

namespace App\Enums\Payment;

enum PaymentProviderEnum: string
{
    case STRIPE = 'stripe';
    case FAKE = 'fake';

    public function label(): string
    {
        return match ($this) {
            self::STRIPE => 'Stripe',
            self::FAKE => 'Fake Payment',
        };
    }
}
