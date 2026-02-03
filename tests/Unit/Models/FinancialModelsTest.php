<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\Order\OrderStatusEnum;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Enums\Transaction\TransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;

test('User model has correct financial relationships and casts', function (): void {
    $user = User::factory()->create(['balance' => 1000]);

    expect($user->balance)->toBeInstanceOf(Money::class)
        ->and($user->balance->getAmount())->toBe('1000');

    $payoutMethod = PayoutMethod::factory()->create(['user_id' => $user->id]);
    expect($user->payoutMethods)->toHaveCount(1)
        ->and($user->payoutMethods->first()->id)->toBe($payoutMethod->id);

    $transaction = Transaction::factory()->create(['user_id' => $user->id]);
    expect($user->transactions)->toHaveCount(1)
        ->and($user->transactions->first()->id)->toBe($transaction->id);

    Order::factory()->create(['user_id' => $user->id]);
    expect($user->orders)->toHaveCount(1);
});

test('Transaction model casts type correctly', function (): void {
    $transaction = Transaction::factory()->create([
        'type' => TransactionType::DEPOSIT,
        'metadata' => ['key' => 'value'],
    ]);

    expect($transaction->type)->toBe(TransactionType::DEPOSIT)
        ->and($transaction->metadata)->toBe(['key' => 'value']);
});

test('Order model casts status and total_amount', function (): void {
    $order = Order::factory()->create([
        'status' => OrderStatusEnum::PAID,
        'total_amount' => 5000,
    ]);

    expect($order->status)->toBe(OrderStatusEnum::PAID)
        ->and($order->total_amount)->toBeInstanceOf(Money::class)
        ->and($order->total_amount->getAmount())->toBe('5000');
});

test('Payment model casts status and provider', function (): void {
    $payment = Payment::factory()->create([
        'status' => PaymentStatusEnum::SUCCEEDED,
        'provider' => PaymentProviderEnum::STRIPE,
    ]);

    expect($payment->status)->toBe(PaymentStatusEnum::SUCCEEDED)
        ->and($payment->provider)->toBe(PaymentProviderEnum::STRIPE);
});

test('PayoutMethod model casts provider', function (): void {
    $method = PayoutMethod::factory()->create([
        'provider' => PaymentProviderEnum::STRIPE,
    ]);

    expect($method->provider)->toBe(PaymentProviderEnum::STRIPE);
});
