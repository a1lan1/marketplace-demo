<?php

declare(strict_types=1);

namespace App\Contracts\Services\Payment;

use App\DTO\PurchaseDTO;
use App\Models\Order;
use Cknow\Money\Money;
use Throwable;

interface PaymentProcessorInterface
{
    /**
     * Processes the payment for a given order.
     *
     * @throws Throwable
     */
    public function process(PurchaseDTO $purchaseDTO, Order $order, Money $totalAmount): void;
}
