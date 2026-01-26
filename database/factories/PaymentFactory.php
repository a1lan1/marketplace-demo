<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'amount' => fake()->numberBetween(1000, 10000),
            'currency' => 'USD',
            'status' => 'succeeded',
            'provider' => 'fake',
            'transaction_id' => 'pi_'.fake()->unique()->lexify('??????????????'),
        ];
    }
}
