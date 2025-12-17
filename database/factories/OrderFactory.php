<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'total_amount' => fake()->numberBetween(5000, 200000),
            'status' => fake()->randomElement(OrderStatusEnum::cases()),
        ];
    }
}
