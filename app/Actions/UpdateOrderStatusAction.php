<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatusEnum $status): void
    {
        $order->update(['status' => $status]);

        event(new OrderStatusChanged($order));
    }
}
