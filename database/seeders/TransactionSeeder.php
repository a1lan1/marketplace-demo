<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Collection<int, Order> $orders */
        $orders = Order::with('buyer')
            ->where('status', OrderStatusEnum::PENDING)
            ->whereDoesntHave('payment')
            ->whereDoesntHave('transaction')
            ->get();

        foreach ($orders as $order) {
            Transaction::factory()
                ->for($order->buyer, 'user')
                ->for($order)
                ->create([
                    'amount' => $order->total_amount->getAmount(),
                    'type' => TransactionType::PURCHASE,
                    'description' => 'Payment for order #'.$order->id,
                ]);

            $order->updateStatus(OrderStatusEnum::PAID);
        }
    }
}
