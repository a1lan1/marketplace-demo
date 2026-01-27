<?php

declare(strict_types=1);

namespace App\Services\PaymentProcessors;

use App\Contracts\BalanceServiceInterface;
use App\Contracts\Services\Payment\PaymentProcessorInterface;
use App\DTO\PurchaseDTO;
use App\DTO\PurchaseOnBalanceDTO;
use App\Events\FundsWithdrawnFromBalance;
use App\Models\Order;
use Cknow\Money\Money;
use Throwable;

readonly class BalancePaymentProcessor implements PaymentProcessorInterface
{
    public function __construct(private BalanceServiceInterface $balanceService) {}

    /**
     * @throws Throwable
     */
    public function process(PurchaseDTO $purchaseDTO, Order $order, Money $totalAmount): void
    {
        $this->balanceService->purchase(
            new PurchaseOnBalanceDTO(
                user: $purchaseDTO->buyer,
                amount: $totalAmount,
                order: $order,
                description: 'Payment for order #'.$order->id
            )
        );

        event(new FundsWithdrawnFromBalance(
            buyer: $purchaseDTO->buyer,
            order: $order,
            totalAmount: $totalAmount
        ));
    }
}
