<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\PaymentCustomerRepositoryInterface;
use App\Contracts\Repositories\PaymentMethodRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Enums\PaymentProviderEnum;
use App\Repositories\PaymentCustomerRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\PaymentRepository;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use App\Services\PaymentProcessors\PaymentProcessorFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Override;
use RuntimeException;
use Stripe\StripeClient;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [
        PaymentRepositoryInterface::class => PaymentRepository::class,
        PaymentMethodRepositoryInterface::class => PaymentMethodRepository::class,
        PaymentCustomerRepositoryInterface::class => PaymentCustomerRepository::class,
    ];

    /**
     * Register services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(function (): StripeClient {
            return new StripeClient(config('services.stripe.secret'));
        });

        $this->app->singleton(PaymentGatewayFactory::class);
        $this->app->singleton(PaymentProcessorFactory::class);

        $this->app->singleton(function (Application $app): PaymentService {
            $defaultProvider = config('services.payments.default_provider');

            if (! is_string($defaultProvider)) {
                throw new RuntimeException('Default payment provider is not configured.');
            }

            $providerEnum = PaymentProviderEnum::tryFrom($defaultProvider);

            if (! $providerEnum) {
                throw new RuntimeException('Invalid default payment provider configured: '.$defaultProvider);
            }

            $factory = $app->make(PaymentGatewayFactory::class);

            return new PaymentService(
                paymentGatewayFactory: $factory,
                paymentRepository: $app->make(PaymentRepositoryInterface::class),
                paymentCustomerRepository: $app->make(PaymentCustomerRepositoryInterface::class),
                paymentMethodRepository: $app->make(PaymentMethodRepositoryInterface::class),
                paymentGateway: $factory->make($providerEnum)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
