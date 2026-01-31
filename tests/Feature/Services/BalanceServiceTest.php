<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Services\BalanceServiceInterface;
use App\Contracts\Services\Payment\PaymentGatewayInterface;
use App\DTO\Balance\DepositDTO;
use App\DTO\Balance\WithdrawDTO;
use App\DTO\Payment\PayoutResultDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Transaction\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Models\PayoutMethod;
use App\Models\User;
use App\Services\Payment\PaymentGatewayFactory;
use Cknow\Money\Money;
use Mockery;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\mock;

beforeEach(function (): void {
    $this->balanceService = resolve(BalanceServiceInterface::class);
});

it('can deposit funds', function (): void {
    $user = User::factory()->create(['balance' => 0]);
    $amount = Money::USD(1000);

    $dto = new DepositDTO(
        user: $user,
        amount: $amount,
        description: 'Test Deposit'
    );

    $transaction = $this->balanceService->deposit($dto);

    expect($user->refresh()->balance->getAmount())->toBe('1000');
    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT->value,
        'amount' => 1000,
        'description' => 'Test Deposit',
    ]);
});

it('can withdraw funds', function (): void {
    $user = User::factory()->create(['balance' => 2000]);
    $amount = Money::USD(1000);
    $payoutMethod = PayoutMethod::factory()->create([
        'user_id' => $user->id,
        'provider' => PaymentProviderEnum::FAKE,
    ]);

    // Mock Gateway
    $gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
    $gatewayMock->shouldReceive('createPayout')
        ->once()
        ->andReturn(new PayoutResultDTO(
            payoutId: 'po_123',
            status: 'pending',
            amount: 1000,
            currency: 'USD',
            arrivalDate: '2023-01-01'
        ));

    mock(PaymentGatewayFactory::class, function ($mock) use ($gatewayMock): void {
        $mock->shouldReceive('make')->andReturn($gatewayMock);
    });

    // Re-resolve service to use mocked factory
    $balanceService = resolve(BalanceServiceInterface::class);

    $dto = new WithdrawDTO(
        user: $user,
        amount: $amount,
        payoutMethod: $payoutMethod,
        description: 'Test Withdrawal'
    );

    $transaction = $balanceService->withdraw($dto);

    expect($user->refresh()->balance->getAmount())->toBe('1000');
    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'type' => TransactionType::WITHDRAWAL->value,
        'amount' => 1000,
        'description' => 'Test Withdrawal',
    ]);
});

it('throws exception when insufficient funds', function (): void {
    $user = User::factory()->create(['balance' => 500]);
    $amount = Money::USD(1000);
    $payoutMethod = PayoutMethod::factory()->create([
        'user_id' => $user->id,
        'provider' => PaymentProviderEnum::FAKE,
    ]);

    $dto = new WithdrawDTO(
        user: $user,
        amount: $amount,
        payoutMethod: $payoutMethod
    );

    $this->balanceService->withdraw($dto);
})->throws(InsufficientFundsException::class);

it('has sufficient funds check', function (): void {
    $user = User::factory()->create(['balance' => 1000]);

    expect($this->balanceService->hasSufficientFunds($user, Money::USD(500)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(1000)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(1001)))->toBeFalse();
});
