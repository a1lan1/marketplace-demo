<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\Services\Payment\PaymentGatewayInterface;
use App\Enums\Payment\PaymentProviderEnum;
use App\Services\Payment\Gateways\FakePaymentGateway;
use App\Services\Payment\Gateways\StripePaymentGateway;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;

class PaymentGatewayFactory
{
    public function __construct(private readonly Container $container) {}

    /**
     * @throws BindingResolutionException
     */
    public function make(PaymentProviderEnum $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            PaymentProviderEnum::FAKE => $this->container->make(FakePaymentGateway::class),
            PaymentProviderEnum::STRIPE => $this->container->make(StripePaymentGateway::class),
        };
    }
}
