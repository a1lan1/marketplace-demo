<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payments;

use App\Contracts\Repositories\PaymentCustomerRepositoryInterface;
use App\Contracts\Repositories\PaymentMethodRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Services\Payment\PaymentGatewayInterface;
use App\DTO\Payment\GatewayChargeResultDTO;
use App\DTO\Payment\ProcessPaymentDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Models\Payment;
use App\Models\PaymentCustomer;
use App\Models\User;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mockery;

beforeEach(function (): void {
    Event::fake();
});

it('can process a payment using mocks', function (): void {
    // Arrange
    $user = User::factory()->make(['id' => 1]);
    $payment = Payment::factory()->make(['id' => 1, 'status' => PaymentStatusEnum::PENDING]);

    $gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
    $factoryMock = Mockery::mock(PaymentGatewayFactory::class);
    $paymentRepoMock = Mockery::mock(PaymentRepositoryInterface::class);
    $customerRepoMock = Mockery::mock(PaymentCustomerRepositoryInterface::class);
    $methodRepoMock = Mockery::mock(PaymentMethodRepositoryInterface::class);

    $factoryMock->shouldReceive('make')->with(PaymentProviderEnum::STRIPE)->andReturn($gatewayMock);

    $customerRepoMock->shouldReceive('findByUserIdAndProvider')->andReturn(null);
    $gatewayMock->shouldReceive('createCustomer')->andReturn('cus_123');
    $customerRepoMock->shouldReceive('create');

    $paymentRepoMock->shouldReceive('findByIdempotencyKey')->andReturn(null);
    $paymentRepoMock->shouldReceive('create')->andReturn($payment);

    $gatewayMock->shouldReceive('charge')->andReturn(new GatewayChargeResultDTO(
        transactionId: 'pi_123',
        status: 'succeeded',
        amount: 1000,
        currency: 'USD',
        clientSecret: 'secret'
    ));

    $paymentRepoMock->shouldReceive('updateStatus')->once();

    $service = new PaymentService(
        $factoryMock,
        $paymentRepoMock,
        $customerRepoMock,
        $methodRepoMock,
        $gatewayMock
    );

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 1000,
        'currency' => 'USD',
        'paymentMethodId' => 'pm_123', // Token
        'saveCard' => false,
        'provider' => PaymentProviderEnum::STRIPE,
        'idempotencyKey' => Str::uuid()->toString(),
    ]);

    // Act
    $result = $service->processPayment($dto);

    // Assert
    expect($result->status)->toBe(PaymentStatusEnum::PENDING);
});

it('handles gateway exception in unit test', function (): void {
    $user = User::factory()->make(['id' => 1]);
    $payment = Payment::factory()->make(['id' => 1]);

    $gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
    $factoryMock = Mockery::mock(PaymentGatewayFactory::class);
    $paymentRepoMock = Mockery::mock(PaymentRepositoryInterface::class);
    $customerRepoMock = Mockery::mock(PaymentCustomerRepositoryInterface::class);
    $methodRepoMock = Mockery::mock(PaymentMethodRepositoryInterface::class);

    $factoryMock->shouldReceive('make')->andReturn($gatewayMock);
    $customerRepoMock->shouldReceive('findByUserIdAndProvider')->andReturn(new PaymentCustomer(['provider_customer_id' => 'cus_123']));

    $paymentRepoMock->shouldReceive('findByIdempotencyKey')->andReturn(null);
    $paymentRepoMock->shouldReceive('create')->andReturn($payment);

    $gatewayMock->shouldReceive('charge')->andThrow(new PaymentGatewayException('Fail'));

    $paymentRepoMock->shouldReceive('updateStatus')->withArgs(function ($p, $dto): bool {
        return $dto->status === PaymentStatusEnum::FAILED;
    })->once();

    $service = new PaymentService(
        $factoryMock,
        $paymentRepoMock,
        $customerRepoMock,
        $methodRepoMock,
        $gatewayMock
    );

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 1000,
        'currency' => 'USD',
        'paymentMethodId' => 'pm_123', // Token
        'saveCard' => false,
        'provider' => PaymentProviderEnum::STRIPE,
    ]);

    try {
        $service->processPayment($dto);
    } catch (PaymentGatewayException $paymentGatewayException) {
        expect($paymentGatewayException)->toBeInstanceOf(PaymentGatewayException::class);

        return;
    }

    $this->fail('PaymentGatewayException was not thrown');
});
