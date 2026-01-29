<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\Balance\DepositDTO;
use App\DTO\Payment\ProcessPaymentDTO;
use App\DTO\Payment\ProcessPaymentResultDTO;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\PaymentGatewayException;
use App\Services\Payment\PaymentService;
use Throwable;

class DepositAction
{
    public function __construct(
        protected PaymentService $paymentService,
        protected BalanceServiceInterface $balanceService,
    ) {}

    /**
     * @throws PaymentGatewayException
     * @throws Throwable
     */
    public function execute(ProcessPaymentDTO $dto): ProcessPaymentResultDTO
    {
        $result = $this->paymentService->processPayment($dto);

        if ($result->status === PaymentStatusEnum::SUCCEEDED) {
            $this->balanceService->deposit(
                new DepositDTO(
                    user: $dto->user,
                    amount: $dto->getMoney(),
                    description: 'Deposit via '.$dto->provider->value
                )
            );
        }

        return $result;
    }
}
