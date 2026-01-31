<?php

use App\DTO\Payment\PaymentMethodCreateDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Repositories\PaymentMethodRepository;
use Carbon\CarbonImmutable;

it('creates a payment method', function (): void {
    $user = User::factory()->create();
    $dto = new PaymentMethodCreateDTO(
        userId: $user->id,
        type: 'card',
        provider: PaymentProviderEnum::FAKE,
        providerId: 'pm_123',
        lastFour: '4242',
        brand: 'visa',
        expiresAt: CarbonImmutable::now()->addYear()
    );

    $repository = new PaymentMethodRepository;
    $method = $repository->create($dto);

    expect($method)->toBeInstanceOf(PaymentMethod::class)
        ->and($method->user_id)->toBe($user->id);

    $this->assertDatabaseHas('payment_methods', ['id' => $method->id]);
});

it('finds a payment method by id', function (): void {
    $method = PaymentMethod::factory()->create();

    $repository = new PaymentMethodRepository;
    $foundMethod = $repository->findById($method->id);

    expect($foundMethod)->toBeInstanceOf(PaymentMethod::class)
        ->and($foundMethod->id)->toBe($method->id);
});
