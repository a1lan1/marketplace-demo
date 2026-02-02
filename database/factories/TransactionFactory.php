<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Transaction\TransactionType;
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
            'user_id' => User::factory(),
            'order_id' => null,
            'amount' => fake()->numberBetween(1000, 50000),
            'type' => fake()->randomElement([
                TransactionType::DEPOSIT,
                TransactionType::WITHDRAWAL,
                TransactionType::TRANSFER,
            ]),
            'description' => fake()->sentence,
        ];
    }

    public function purchase(): self
    {
        return $this->state(function (array $attributes): array {
            return [
                'type' => TransactionType::PURCHASE,
                'order_id' => Order::factory()->create([
                    'user_id' => $attributes['user_id'],
                ])->id,
            ];
        })->afterCreating(function (Transaction $transaction): void {
            if ($transaction->order) {
                $transaction->order->update(['total_amount' => $transaction->amount]);
                $transaction->description = 'Payment for order #'.$transaction->order_id;
                $transaction->save();
            }
        });
    }
}
