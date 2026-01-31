<?php

declare(strict_types=1);

namespace Tests\Unit\Services\PaymentProcessors;

use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\CartItemDTO;
use App\DTO\Payment\ProcessPaymentResultDTO;
use App\DTO\PurchaseDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Enums\Payment\PaymentTypeEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\PaymentProcessors\BalancePaymentProcessor;
use App\Services\PaymentProcessors\CardPaymentProcessor;
use Cknow\Money\Money;
use InvalidArgumentException;
use Mockery;
use Spatie\LaravelData\DataCollection;

test('BalancePaymentProcessor throws exception if user not found', function (): void {
    $balanceService = Mockery::mock(BalanceServiceInterface::class);
    $processor = new BalancePaymentProcessor($balanceService);

    $user = User::factory()->make(['id' => 1]);
    $dto = new PurchaseDTO(
        buyer: $user,
        cart: new DataCollection(CartItemDTO::class, []),
        paymentType: PaymentTypeEnum::BALANCE,
    );
    $order = Order::factory()->make();
    $total = Money::USD(1000);

    $balanceService->shouldReceive('purchase')->once();

    $processor->process($dto, $order, $total);
    expect(true)->toBeTrue();
});

test('CardPaymentProcessor throws exception if provider missing', function (): void {
    $paymentService = Mockery::mock(PaymentService::class);
    $processor = new CardPaymentProcessor($paymentService);

    $user = User::factory()->make();
    $dto = new PurchaseDTO(
        buyer: $user,
        cart: new DataCollection(CartItemDTO::class, []),
        paymentType: PaymentTypeEnum::CARD, // Missing provider
        paymentMethodId: 'pm_123'
    );
    $order = Order::factory()->make();
    $total = Money::USD(1000);

    $processor->process($dto, $order, $total);
})->throws(InvalidArgumentException::class);

test('CardPaymentProcessor calls payment service', function (): void {
    $paymentService = Mockery::mock(PaymentService::class);
    $processor = new CardPaymentProcessor($paymentService);

    $user = User::factory()->make();
    $dto = new PurchaseDTO(
        buyer: $user,
        cart: new DataCollection(CartItemDTO::class, []),
        paymentType: PaymentTypeEnum::CARD,
        paymentMethodId: 'pm_123',
        paymentProvider: PaymentProviderEnum::STRIPE
    );
    $order = Order::factory()->make(['id' => 1]);
    $total = Money::USD(1000);

    new Payment(['status' => PaymentStatusEnum::SUCCEEDED]);

    $resultMock = ProcessPaymentResultDTO::from([
        'status' => PaymentStatusEnum::SUCCEEDED,
        'paymentId' => 'pay_123',
    ]);

    $paymentService->shouldReceive('processPayment')->once()->andReturn($resultMock);
    $paymentService->shouldReceive('linkPaymentToOrder')->once();

    $processor->process($dto, $order, $total);
    expect(true)->toBeTrue();
});
