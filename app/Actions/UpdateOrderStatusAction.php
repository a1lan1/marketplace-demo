<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;

class UpdateOrderStatusAction
{
    public function __construct(protected OrderRepositoryInterface $orderRepository) {}

    public function execute(Order $order, OrderStatusEnum $status): void
    {
        $this->orderRepository->updateStatus($order, $status);

        event(new OrderStatusChanged($order));
    }
}
