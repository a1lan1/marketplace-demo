<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Services\BalanceServiceInterface;
use App\Contracts\Services\Payment\PaymentGatewayInterface;
use App\DTO\Balance\DepositDTO;
use App\DTO\Balance\TransferDTO;
use App\DTO\Balance\WithdrawDTO;
use App\DTO\Payment\PayoutResultDTO;
use App\DTO\PurchaseOnBalanceDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Transaction\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Models\Order;
use App\Models\PayoutMethod;
use App\Models\User;
use App\Services\Payment\PaymentGatewayFactory;
use Cknow\Money\Money;
use Mockery;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
    $this->factoryMock = Mockery::mock(PaymentGatewayFactory::class);
    $this->factoryMock->shouldReceive('make')->andReturn($this->gatewayMock);

    $this->app->instance(PaymentGatewayFactory::class, $this->factoryMock);
    $this->balanceService = $this->app->make(BalanceServiceInterface::class);
});

it('can deposit money and link to an order', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 10000]); // $100.00
    $amount = Money::USD(5000); // $50.00
    $order = Order::factory()->create();

    $dto = new DepositDTO(
        user: $user,
        amount: $amount,
        order: $order,
        description: 'Test Deposit'
    );

    // Act
    $transaction = $this->balanceService->deposit($dto);

    // Assert
    expect((int) $user->fresh()->balance->getAmount())->toBe(15000); // $150.00
    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'amount' => 5000,
        'type' => TransactionType::DEPOSIT->value,
        'description' => 'Test Deposit',
        'order_id' => $order->id,
    ]);
});

it('can withdraw money', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 10000]); // $100.00
    $amount = Money::USD(5000); // $50.00
    $payoutMethod = PayoutMethod::factory()->create([
        'user_id' => $user->id,
        'provider' => PaymentProviderEnum::FAKE,
    ]);

    $this->gatewayMock->shouldReceive('createPayout')
        ->once()
        ->andReturn(new PayoutResultDTO(
            payoutId: 'po_123',
            status: 'pending',
            amount: 5000,
            currency: 'USD',
            arrivalDate: '2023-01-01'
        ));

    $dto = new WithdrawDTO(
        user: $user,
        amount: $amount,
        payoutMethod: $payoutMethod,
        description: 'Test Withdrawal'
    );

    // Act
    $transaction = $this->balanceService->withdraw($dto);

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
    $payoutMethod = PayoutMethod::factory()->create([
        'user_id' => $user->id,
        'provider' => PaymentProviderEnum::FAKE,
    ]);

    $dto = new WithdrawDTO(
        user: $user,
        amount: $amount,
        payoutMethod: $payoutMethod
    );

    // Act & Assert
    $this->balanceService->withdraw($dto);
})->throws(InsufficientFundsException::class);

it('checks sufficient funds correctly', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 10000]);

    // Assert
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(5000)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(10000)))->toBeTrue();
    expect($this->balanceService->hasSufficientFunds($user, Money::USD(15000)))->toBeFalse();
});

it('can transfer money between users', function (): void {
    // Arrange
    $sender = User::factory()->create(['balance' => 10000]);
    $recipient = User::factory()->create(['balance' => 5000]);
    $amount = Money::USD(3000);

    $dto = new TransferDTO(
        sender: $sender,
        recipient: $recipient,
        amount: $amount,
        description: 'Test Transfer'
    );

    // Act
    $result = $this->balanceService->transfer($dto);

    // Assert
    expect((int) $sender->fresh()->balance->getAmount())->toBe(7000);
    expect((int) $recipient->fresh()->balance->getAmount())->toBe(8000);

    assertDatabaseHas('transactions', [
        'id' => $result->senderTransaction->id,
        'user_id' => $sender->id,
        'amount' => 3000,
        'type' => TransactionType::TRANSFER->value,
    ]);
    assertDatabaseHas('transactions', [
        'id' => $result->recipientTransaction->id,
        'user_id' => $recipient->id,
        'amount' => 3000,
        'type' => TransactionType::TRANSFER->value,
    ]);
});

it('throws exception when transferring with insufficient funds', function (): void {
    // Arrange
    $sender = User::factory()->create(['balance' => 2000]);
    $recipient = User::factory()->create(['balance' => 5000]);
    $amount = Money::USD(3000);

    $dto = new TransferDTO(
        sender: $sender,
        recipient: $recipient,
        amount: $amount
    );

    // Act & Assert
    $this->balanceService->transfer($dto);
})->throws(InsufficientFundsException::class);

it('can purchase with balance', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 10000]);
    $amount = Money::USD(5000);
    $order = Order::factory()->create(['user_id' => $user->id, 'total_amount' => 5000]);

    $dto = new PurchaseOnBalanceDTO(
        user: $user,
        amount: $amount,
        order: $order,
        description: 'Test Purchase'
    );

    // Act
    $transaction = $this->balanceService->purchase($dto);

    // Assert
    expect((int) $user->fresh()->balance->getAmount())->toBe(5000);

    assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'user_id' => $user->id,
        'amount' => 5000,
        'type' => TransactionType::PURCHASE->value,
        'description' => 'Test Purchase',
        'order_id' => $order->id,
    ]);
});

it('throws exception when purchasing with insufficient funds', function (): void {
    // Arrange
    $user = User::factory()->create(['balance' => 1000]);
    $amount = Money::USD(5000);
    $order = Order::factory()->create(['user_id' => $user->id]);

    $dto = new PurchaseOnBalanceDTO(
        user: $user,
        amount: $amount,
        order: $order,
        description: 'Test Purchase'
    );

    // Act & Assert
    $this->balanceService->purchase($dto);
})->throws(InsufficientFundsException::class);
