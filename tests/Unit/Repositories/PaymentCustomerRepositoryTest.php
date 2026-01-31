<?php

use App\Enums\Payment\PaymentProviderEnum;
use App\Models\PaymentCustomer;
use App\Models\User;
use App\Repositories\PaymentCustomerRepository;

it('creates a payment customer', function (): void {
    $user = User::factory()->create();
    $provider = PaymentProviderEnum::FAKE;
    $customerId = 'cus_123';

    $repository = new PaymentCustomerRepository;
    $customer = $repository->create($user->id, $provider, $customerId);

    expect($customer)->toBeInstanceOf(PaymentCustomer::class)
        ->and($customer->user_id)->toBe($user->id)
        ->and($customer->provider)->toBe($provider)
        ->and($customer->provider_customer_id)->toBe($customerId);

    $this->assertDatabaseHas('payment_customers', [
        'user_id' => $user->id,
        'provider' => $provider->value,
    ]);
});

it('finds a payment customer by user id and provider', function (): void {
    $user = User::factory()->create();
    $provider = PaymentProviderEnum::STRIPE;
    $customer = PaymentCustomer::factory()->create([
        'user_id' => $user->id,
        'provider' => $provider,
    ]);

    $repository = new PaymentCustomerRepository;
    $foundCustomer = $repository->findByUserIdAndProvider($user->id, $provider);

    expect($foundCustomer)->toBeInstanceOf(PaymentCustomer::class)
        ->and($foundCustomer->id)->toBe($customer->id);
});
