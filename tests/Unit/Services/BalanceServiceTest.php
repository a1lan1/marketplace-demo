<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Actions\Transactions\CreateTransactionAction;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Models\User;
use App\Services\BalanceService;
use Cknow\Money\Money;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->balanceService = new BalanceService(new CreateTransactionAction);
});

it('can deposit money', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 10000]); // $100.00
    $amount = Money::USD(5000); // $50.00

    // Act
    $transaction = $this->balanceService->deposit($user, $amount, 'Test Deposit');

    // Assert
    expect((int) $user->fresh()->balance->getAmount())->toBe(15000); // $150.00
    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'amount' => 5000,
        'type' => TransactionType::DEPOSIT->value,
        'description' => 'Test Deposit',
    ]);
});

it('can withdraw money', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 10000]); // $100.00
    $amount = Money::USD(5000); // $50.00

    // Act
    $transaction = $this->balanceService->withdraw($user, $amount, 'Test Withdrawal');

    // Assert
    expect((int) $user->fresh()->balance->getAmount())->toBe(5000); // $50.00
    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'amount' => 5000,
        'type' => TransactionType::WITHDRAWAL->value,
        'description' => 'Test Withdrawal',
    ]);
});

it('throws exception when withdrawing insufficient funds', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 1000]);
    $amount = Money::USD(5000);

    // Act & Assert
    $this->balanceService->withdraw($user, $amount);
})->throws(InsufficientFundsException::class);

it('checks sufficient funds correctly', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 10000]);

    // Assert
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(5000)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(10000)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(15000)))->toBeFalse();
});
