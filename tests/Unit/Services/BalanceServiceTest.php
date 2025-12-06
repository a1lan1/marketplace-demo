<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Models\User;
use App\Services\BalanceService;
use Cknow\Money\Money;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->balanceService = new BalanceService;
});

it('can deposit money', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => Money::USD(10000)]); // $100.00
    $amount = Money::USD(5000); // $50.00

    // Act
    $transaction = $this->balanceService->deposit($user, $amount, 'Test Deposit');

    // Assert
    expect((int) $user->fresh()->balance->getAmount())->toBe(15000); // $150.00
    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'amount' => '50.00', // Changed to string
        'type' => TransactionType::DEPOSIT->value,
        'description' => 'Test Deposit',
    ]);
});

it('can withdraw money', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => Money::USD(10000)]); // $100.00
    $amount = Money::USD(5000); // $50.00

    // Act
    $transaction = $this->balanceService->withdraw($user, $amount, 'Test Withdrawal');

    // Assert
    expect((int) $user->fresh()->balance->getAmount())->toBe(5000); // $50.00
    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'amount' => '-50.00', // Changed to string
        'type' => TransactionType::WITHDRAWAL->value,
        'description' => 'Test Withdrawal',
    ]);
});

it('throws exception when withdrawing insufficient funds', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => Money::USD(10)]);
    $amount = Money::USD(50);

    // Act & Assert
    $this->balanceService->withdraw($user, $amount);
})->throws(InsufficientFundsException::class);

it('checks sufficient funds correctly', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => Money::USD(100)]);

    // Assert
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(50)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(100)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(150)))->toBeFalse();
});
