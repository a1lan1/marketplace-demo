<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'order_id' => Order::factory(),
            'amount' => fake()->numberBetween(10000, 100000),
            'type' => fake()->randomElement(TransactionType::cases()),
        ];
    }
}
