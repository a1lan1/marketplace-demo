<?php

use App\DTO\Payment\PaymentUpdateDTO;
use App\DTO\Payment\ProcessPaymentDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Repositories\PaymentRepository;

it('creates a payment', function (): void {
    $user = User::factory()->create();
    $dto = ProcessPaymentDTO::make([
        'user' => $user,
        'amount' => 1000,
        'currency' => 'USD',
        'paymentMethodId' => 'pm_fake',
        'saveCard' => false,
        'provider' => PaymentProviderEnum::FAKE,
        'status' => PaymentStatusEnum::PENDING,
    ]);

    $repository = new PaymentRepository;
    $payment = $repository->create($dto);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->amount->getAmount())->toBe('1000');

    $this->assertDatabaseHas('payments', ['id' => $payment->id]);
});

it('updates payment status', function (): void {
    $payment = Payment::factory()->create(['status' => PaymentStatusEnum::PENDING]);
    $dto = new PaymentUpdateDTO(status: PaymentStatusEnum::SUCCEEDED, transactionId: 'txn_123');

    $repository = new PaymentRepository;
    $updatedPayment = $repository->updateStatus($payment, $dto);

    expect($updatedPayment->status)->toBe(PaymentStatusEnum::SUCCEEDED)
        ->and($updatedPayment->transaction_id)->toBe('txn_123');
});

it('finds payment by idempotency key', function (): void {
    $user = User::factory()->create();
    Payment::factory()->create(['idempotency_key' => 'idem-key', 'user_id' => $user->id]);

    $repository = new PaymentRepository;
    $foundPayment = $repository->findByIdempotencyKey('idem-key', $user->id);

    expect($foundPayment)->toBeInstanceOf(Payment::class);
});

it('gets user payment methods', function (): void {
    $user = User::factory()->create();
    PaymentMethod::factory()->count(3)->create(['user_id' => $user->id]);

    $repository = new PaymentRepository;
    $methods = $repository->getUserPaymentMethods($user->id);

    expect($methods)->toHaveCount(3);
});
