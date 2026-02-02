<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Order\OrderStatusEnum;
use App\Enums\Transaction\TransactionType;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $pendingOrders = Order::with('buyer')
            ->where('status', OrderStatusEnum::PENDING)
            ->whereDoesntHave('transaction')
            ->get();

        foreach ($pendingOrders as $order) {
            Transaction::factory()
                ->for($order->buyer, 'user')
                ->for($order)
                ->create([
                    'amount' => $order->total_amount->getAmount(),
                    'type' => TransactionType::PURCHASE,
                    'description' => 'Payment for order #'.$order->id,
                ]);

            $order->update(['status' => OrderStatusEnum::PAID]);
        }

        foreach ($users as $user) {
            Transaction::factory(rand(10, 15))
                ->for($user)
                ->create();
        }
    }
}
