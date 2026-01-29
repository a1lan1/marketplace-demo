<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Contracts\Repositories\PayoutMethodRepositoryInterface;
use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\Balance\WithdrawDTO;
use App\Exceptions\InsufficientFundsException;
use App\Models\User;
use Cknow\Money\Money;
use Throwable;

class WithdrawAction
{
    public function __construct(
        protected BalanceServiceInterface $balanceService,
        protected PayoutMethodRepositoryInterface $payoutMethodRepository,
    ) {}

    /**
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function execute(User $user, int $payoutMethodId, Money $amount, ?string $description): void
    {
        $payoutMethod = $this->payoutMethodRepository->findOrFail($payoutMethodId);

        $this->balanceService->withdraw(
            new WithdrawDTO(
                user: $user,
                amount: $amount,
                payoutMethod: $payoutMethod,
                description: $description
            )
        );
    }
}
