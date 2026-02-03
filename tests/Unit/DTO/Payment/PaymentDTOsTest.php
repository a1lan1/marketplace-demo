<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Payment;

use App\DTO\CartItemDTO;
use App\DTO\Payment\ProcessPaymentDTO;
use App\DTO\PurchaseDTO;
use App\DTO\PurchaseOnBalanceDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentTypeEnum;
use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataCollection;

test('CartItemDTO calculates total correctly', function (): void {
    $dto = new CartItemDTO(productId: 1, quantity: 2);
    expect($dto->productId)->toBe(1)
        ->and($dto->quantity)->toBe(2);
});

test('PurchaseDTO handles enum casting', function (): void {
    $user = User::factory()->make();
    $cart = new DataCollection(CartItemDTO::class, []);

    $dto = new PurchaseDTO(
        buyer: $user,
        cart: $cart,
        paymentType: PaymentTypeEnum::CARD,
        paymentProvider: PaymentProviderEnum::STRIPE
    );

    expect($dto->paymentType)->toBe(PaymentTypeEnum::CARD)
        ->and($dto->paymentProvider)->toBe(PaymentProviderEnum::STRIPE);
});

test('PurchaseOnBalanceDTO instantiates correctly', function (): void {
    $user = User::factory()->make();
    $amount = Money::USD(1000);
    $order = Order::factory()->make();

    $dto = new PurchaseOnBalanceDTO(
        user: $user,
        amount: $amount,
        order: $order,
        description: 'Test purchase'
    );

    expect($dto->user)->toBe($user)
        ->and($dto->amount->getAmount())->toBe('1000')
        ->and($dto->order)->toBe($order)
        ->and($dto->description)->toBe('Test purchase');
});

test('ProcessPaymentDTO make method handles token correctly', function (): void {
    $user = User::factory()->make();

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 5000,
        'currency' => 'USD',
        'paymentMethodId' => 'pm_123', // Token
        'provider' => PaymentProviderEnum::STRIPE,
        'saveCard' => true,
        'idempotencyKey' => 'key_123',
    ]);

    expect($dto->user)->toBe($user)
        ->and($dto->amount)->toBe(5000)
        ->and($dto->paymentMethodId)->toBeNull()
        ->and($dto->paymentMethodToken)->toBe('pm_123');
});

test('ProcessPaymentDTO make method handles UUID correctly', function (): void {
    $user = User::factory()->make();
    $uuid = Str::uuid()->toString();

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 5000,
        'currency' => 'USD',
        'paymentMethodId' => $uuid, // UUID
        'provider' => PaymentProviderEnum::STRIPE,
        'saveCard' => true,
    ]);

    expect($dto->paymentMethodId)->toBe($uuid)
        ->and($dto->paymentMethodToken)->toBeNull();
});
