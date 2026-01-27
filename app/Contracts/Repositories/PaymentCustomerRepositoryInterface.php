<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\PaymentProviderEnum;
use App\Models\PaymentCustomer;

interface PaymentCustomerRepositoryInterface
{
    public function findByUserIdAndProvider(int $userId, PaymentProviderEnum $provider): ?PaymentCustomer;

    public function create(int $userId, PaymentProviderEnum $provider, string $providerCustomerId): PaymentCustomer;
}
