<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Purchase;

use App\Contracts\BalanceServiceInterface;
use App\Exceptions\PayoutException;
use App\Models\Order;
use App\Models\User;
use App\Services\Purchase\PayoutDistributor;
use Cknow\Money\Money;
use Mockery;

test('distribute throws exception if seller not found in provided collection', function (): void {
    // Arrange
    $balanceService = Mockery::mock(BalanceServiceInterface::class);
    $distributor = new PayoutDistributor($balanceService);

    $order = Mockery::mock(Order::class)->makePartial();
    $order->id = 1;

    $sellerPayouts = collect([
        1 => Money::USD(1000),
    ]);

    // Empty sellers collection
    $sellers = collect([]);

    // Act & Assert
    expect(fn () => $distributor->distribute($order, $sellerPayouts, $sellers))
        ->toThrow(PayoutException::class, 'Seller with ID 1 not found for order #1');
});

test('distribute deposits to seller balance', function (): void {
    // Arrange
    $balanceService = Mockery::mock(BalanceServiceInterface::class);
    $distributor = new PayoutDistributor($balanceService);

    $order = Mockery::mock(Order::class)->makePartial();
    $order->id = 123;

    $seller = Mockery::mock(User::class)->makePartial();
    $seller->id = 1;

    $amount = Money::USD(1000);
    $sellerPayouts = collect([
        1 => $amount,
    ]);

    $sellers = collect([
        1 => $seller,
    ]);

    $balanceService->shouldReceive('deposit')
        ->once()
        ->with($seller, $amount, 'Payout for order #123');

    // Act
    $distributor->distribute($order, $sellerPayouts, $sellers);

    // Assert
    $this->assertTrue(true); // Mockery assertion
});
