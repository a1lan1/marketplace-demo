<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\PaymentCustomerRepositoryInterface;
use App\Contracts\Repositories\PaymentMethodRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Repositories\PaymentCustomerRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\ServiceProvider;
use Override;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
