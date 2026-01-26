<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $allProducts = Product::all();
        $buyers = User::role(RoleEnum::BUYER)->get();

        $buyers->each(function (User $buyer) use ($allProducts): void {
            Order::factory(rand(10, 20))
                ->for($buyer, 'buyer')
                ->create()
                ->each(function (Order $order) use ($allProducts): void {
                    $productsForOrder = $allProducts->random(rand(1, 3));

                    foreach ($productsForOrder as $product) {
                        $order->products()->attach($product->id, [
                            'quantity' => rand(1, 5),
                            'price' => $product->price->getAmount(),
                        ]);
                    }
                });
        });
    }
}
