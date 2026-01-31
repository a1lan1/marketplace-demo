<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payments\Gateways;

use App\DTO\Payment\PaymentChargeDTO;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Models\User;
use App\Services\Payment\Gateways\FakePaymentGateway;

test('it charges successfully', function (): void {
    $gateway = new FakePaymentGateway;
    $user = User::factory()->make();

    $dto = new PaymentChargeDTO(
        user: $user,
        amount: 1000,
        currency: 'USD',
        customerId: 'cus_123',
        paymentMethodToken: 'pm_123',
        saveCard: false
    );

    $result = $gateway->charge($dto);

    expect($result->status)->toBe('succeeded')
        ->and($result->amount)->toBe(1000)
        ->and($result->currency)->toBe('USD');
});

test('it throws exception when configured to fail', function (): void {
    $gateway = new FakePaymentGateway;
    $user = User::factory()->make();

    $dto = new PaymentChargeDTO(
        user: $user,
        amount: 1000,
        currency: 'USD',
        customerId: 'cus_123',
        paymentMethodToken: 'pm_123',
        saveCard: false,
        options: ['should_fail' => true]
    );

    $gateway->charge($dto);
})->throws(PaymentGatewayException::class);

test('it creates mock customer and payout', function (): void {
    $gateway = new FakePaymentGateway;
    $user = User::factory()->make();

    $customerId = $gateway->createCustomer($user);
    expect($customerId)->toBeString()->and($customerId)->toContain('fake_cus_');
});
