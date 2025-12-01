<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $allProducts = Product::all();

        Order::factory(20)
            ->create()
            ->each(function (Order $order) use ($allProducts): void {
                $productsForOrder = $allProducts->random(fake()->numberBetween(1, 3));

                foreach ($productsForOrder as $product) {
                    $order->products()->attach($product->id, [
                        'quantity' => fake()->numberBetween(1, 5),
                        'price' => $product->price,
                    ]);
                }
            });
    }
}
