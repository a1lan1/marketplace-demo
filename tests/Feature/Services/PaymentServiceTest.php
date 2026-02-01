<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

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
use App\Models\User;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

use function Pest\Laravel\mock;

beforeEach(function (): void {
    DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());

    Event::fake();

    $this->gatewayMock = mock(PaymentGatewayInterface::class);
    $this->paymentRepositoryMock = mock(PaymentRepositoryInterface::class);
    $this->customerRepositoryMock = mock(PaymentCustomerRepositoryInterface::class);
    $this->methodRepositoryMock = mock(PaymentMethodRepositoryInterface::class);

    $factoryMock = mock(PaymentGatewayFactory::class);
    $factoryMock->shouldReceive('make')->andReturn($this->gatewayMock);

    $this->paymentService = new PaymentService(
        $factoryMock,
        $this->paymentRepositoryMock,
        $this->customerRepositoryMock,
        $this->methodRepositoryMock,
        $this->gatewayMock
    );
});

it('processes payment successfully', function (): void {
    $user = User::factory()->create();
    $idempotencyKey = Str::uuid()->toString();
    $payment = Payment::factory()->make(['id' => 1, 'status' => PaymentStatusEnum::SUCCEEDED]);

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 10000,
        'currency' => 'USD',
        'provider' => PaymentProviderEnum::STRIPE,
        'paymentMethodId' => 'pm_card_visa', // This is a token
        'idempotencyKey' => $idempotencyKey,
        'saveCard' => false,
    ]);

    $this->paymentRepositoryMock->shouldReceive('findByIdempotencyKey')->once()->with($idempotencyKey, $user->id)->andReturnNull();
    $this->customerRepositoryMock->shouldReceive('findByUserIdAndProvider')->once()->andReturnNull();
    $this->gatewayMock->shouldReceive('createCustomer')->once()->andReturn('cus_123');
    $this->customerRepositoryMock->shouldReceive('create')->once();
    $this->paymentRepositoryMock->shouldReceive('create')->once()->andReturn($payment);

    $chargeResult = GatewayChargeResultDTO::from([
        'transactionId' => 'pi_123',
        'status' => 'succeeded',
        'amount' => 10000,
        'currency' => 'USD',
        'clientSecret' => null,
    ]);
    $this->gatewayMock->shouldReceive('charge')->once()->andReturn($chargeResult);
    $this->paymentRepositoryMock->shouldReceive('updateStatus')->once();

    $result = $this->paymentService->processPayment($dto);

    expect($result->paymentId)->toEqual($payment->id)
        ->and($result->status)->toBe(PaymentStatusEnum::SUCCEEDED);
});

it('handles idempotency', function (): void {
    $user = User::factory()->create();
    $idempotencyKey = Str::uuid()->toString();
    $existingPayment = Payment::factory()->make(['id' => 1, 'status' => PaymentStatusEnum::SUCCEEDED]);

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 10000,
        'currency' => 'USD',
        'provider' => PaymentProviderEnum::STRIPE,
        'paymentMethodId' => 'pm_card_visa',
        'idempotencyKey' => $idempotencyKey,
        'saveCard' => false,
    ]);

    $this->paymentRepositoryMock->shouldReceive('findByIdempotencyKey')->once()->with($idempotencyKey, $user->id)->andReturn($existingPayment);

    $result = $this->paymentService->processPayment($dto);

    expect($result->paymentId)->toEqual($existingPayment->id)
        ->and($result->status)->toBe($existingPayment->status)
        ->and($result->message)->toBe('Replayed result for idempotent request.');
});

it('handles payment gateway exception', function (): void {
    $user = User::factory()->create();
    $idempotencyKey = Str::uuid()->toString();
    $payment = Payment::factory()->make(['id' => 1, 'status' => PaymentStatusEnum::PENDING]);

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 10000,
        'currency' => 'USD',
        'provider' => PaymentProviderEnum::STRIPE,
        'paymentMethodId' => 'pm_card_visa',
        'idempotencyKey' => $idempotencyKey,
        'saveCard' => false,
    ]);

    $this->paymentRepositoryMock->shouldReceive('findByIdempotencyKey')->andReturnNull();
    $this->customerRepositoryMock->shouldReceive('findByUserIdAndProvider')->andReturnNull();
    $this->gatewayMock->shouldReceive('createCustomer')->andReturn('cus_123');
    $this->customerRepositoryMock->shouldReceive('create');
    $this->paymentRepositoryMock->shouldReceive('create')->andReturn($payment);

    $this->gatewayMock->shouldReceive('charge')->once()->andThrow(new PaymentGatewayException('Payment failed'));
    $this->paymentRepositoryMock->shouldReceive('updateStatus')->once()->withArgs(function ($p, $updateDto): bool {
        return $updateDto->status === PaymentStatusEnum::FAILED;
    });

    $this->paymentService->processPayment($dto);
})->throws(PaymentGatewayException::class);

it('processes payment and persists to database', function (): void {
    // Arrange
    $user = User::factory()->create();
    $idempotencyKey = Str::uuid()->toString();
    $payment = Payment::factory()->make([
        'id' => 1,
        'user_id' => $user->id,
        'amount' => 1000,
        'status' => PaymentStatusEnum::SUCCEEDED,
        'transaction_id' => 'pi_test',
    ]);

    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 1000,
        'currency' => 'USD',
        'paymentMethodId' => 'pm_card',
        'saveCard' => false,
        'provider' => PaymentProviderEnum::STRIPE,
        'idempotencyKey' => $idempotencyKey,
    ]);

    $this->paymentRepositoryMock->shouldReceive('findByIdempotencyKey')->once()->with($idempotencyKey, $user->id)->andReturnNull();
    $this->customerRepositoryMock->shouldReceive('findByUserIdAndProvider')->once()->andReturnNull();
    $this->gatewayMock->shouldReceive('createCustomer')->once()->andReturn('cus_test');
    $this->customerRepositoryMock->shouldReceive('create')->once();
    $this->paymentRepositoryMock->shouldReceive('create')->once()->andReturn($payment);

    $chargeResult = new GatewayChargeResultDTO(
        transactionId: 'pi_test',
        status: 'succeeded',
        amount: 1000,
        currency: 'USD',
        clientSecret: 'secret'
    );
    $this->gatewayMock->shouldReceive('charge')->once()->andReturn($chargeResult);
    $this->paymentRepositoryMock->shouldReceive('updateStatus')->once();

    // Act
    $result = $this->paymentService->processPayment($dto);

    // Assert
    expect($result->status)->toBe(PaymentStatusEnum::SUCCEEDED);
});
