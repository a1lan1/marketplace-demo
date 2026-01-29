<?php

declare(strict_types=1);

namespace App\Services\PaymentProcessors;

use App\Contracts\Services\Payment\PaymentProcessorInterface;
use App\Enums\Payment\PaymentTypeEnum;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;

final readonly class PaymentProcessorFactory
{
    public function __construct(private Application $app) {}

    /**
     * @throws BindingResolutionException
     */
    public function make(PaymentTypeEnum $paymentType): PaymentProcessorInterface
    {
        return match ($paymentType) {
            PaymentTypeEnum::BALANCE => $this->app->make(BalancePaymentProcessor::class),
            PaymentTypeEnum::CARD => $this->app->make(CardPaymentProcessor::class),
        };
    }
}
