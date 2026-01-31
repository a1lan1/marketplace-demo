<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Payment;

use App\Actions\Payment\WithdrawAction;
use App\Contracts\Repositories\PayoutMethodRepositoryInterface;
use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\Balance\WithdrawDTO;
use App\Models\PayoutMethod;
use App\Models\User;
use Cknow\Money\Money;
use Mockery;

it('correctly calls balance service to withdraw funds', function (): void {
    // Arrange
    $user = User::factory()->create();
    $payoutMethod = PayoutMethod::factory()->create(['user_id' => $user->id]);
    $amount = Money::USD(1000);
    $description = 'Test withdrawal';

    $balanceServiceMock = Mockery::mock(BalanceServiceInterface::class);
    $payoutMethodRepositoryMock = Mockery::mock(PayoutMethodRepositoryInterface::class);

    $payoutMethodRepositoryMock->shouldReceive('findOrFail')
        ->with($payoutMethod->id)
        ->once()
        ->andReturn($payoutMethod);

    $balanceServiceMock->shouldReceive('withdraw')
        ->once()
        ->with(Mockery::on(function ($arg) use ($user, $amount, $payoutMethod, $description): bool {
            return $arg instanceof WithdrawDTO &&
                   $arg->user->is($user) &&
                   $arg->amount->equals($amount) &&
                   $arg->payoutMethod->is($payoutMethod) &&
                   $arg->description === $description;
        }));

    $action = new WithdrawAction($balanceServiceMock, $payoutMethodRepositoryMock);

    // Act
    $action->execute($user, $payoutMethod->id, $amount, $description);

    // Assert
    // Mockery assertions are checked automatically.
});
