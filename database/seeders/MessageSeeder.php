<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Collection<int, Order> $orders */
        $orders = Order::has('buyer')
            ->with(['buyer', 'products.seller'])
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            $participants = [$order->buyer];
            $firstProduct = $order->products->first();
            $seller = $firstProduct?->seller;

            if ($seller) {
                $participants[] = $seller;
            }

            Message::factory()->count(5)->create([
                'order_id' => $order->id,
                'user_id' => fn () => fake()->randomElement($participants)->id,
            ]);
        }
    }
}
