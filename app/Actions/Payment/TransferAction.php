<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\Balance\TransferDTO;
use App\DTO\Payment\TransferResultDTO;
use App\Exceptions\InsufficientFundsException;
use App\Models\User;
use Cknow\Money\Money;
use Throwable;

class TransferAction
{
    public function __construct(
        protected BalanceServiceInterface $balanceService,
        protected UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    public function execute(User $sender, int $recipientId, Money $amount, ?string $description): TransferResultDTO
    {
        $recipient = $this->userRepository->findOrFail($recipientId);

        return $this->balanceService->transfer(
            new TransferDTO(
                sender: $sender,
                recipient: $recipient,
                amount: $amount,
                description: $description
            )
        );
    }
}
