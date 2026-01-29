<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PaymentCustomerRepositoryInterface;
use App\Enums\Payment\PaymentProviderEnum;
use App\Models\PaymentCustomer;

class PaymentCustomerRepository implements PaymentCustomerRepositoryInterface
{
    public function findByUserIdAndProvider(int $userId, PaymentProviderEnum $provider): ?PaymentCustomer
    {
        return PaymentCustomer::where('user_id', $userId)
            ->where('provider', $provider)
            ->first();
    }

    public function create(int $userId, PaymentProviderEnum $provider, string $providerCustomerId): PaymentCustomer
    {
        return PaymentCustomer::create([
            'user_id' => $userId,
            'provider' => $provider,
            'provider_customer_id' => $providerCustomerId,
        ]);
    }
}
