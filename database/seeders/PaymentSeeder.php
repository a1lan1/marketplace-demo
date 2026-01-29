<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::with('buyer')
            ->where('status', OrderStatusEnum::PAID)
            ->whereDoesntHave('transaction')
            ->get();

        foreach ($orders as $order) {
            $paymentMethod = PaymentMethod::where('user_id', $order->user_id)
                ->inRandomOrder()
                ->first();

            if ($paymentMethod) {
                Payment::factory()
                    ->for($order)
                    ->for($paymentMethod)
                    ->for($order->buyer, 'user')
                    ->create([
                        'amount' => $order->total_amount->getAmount(),
                    ]);
            }
        }
    }
}
