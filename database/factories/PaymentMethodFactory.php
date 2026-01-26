<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'type' => 'card',
            'provider' => 'fake',
            'provider_id' => 'pm_'.fake()->unique()->lexify('??????????????'),
            'last_four' => fake()->numerify('####'),
            'brand' => 'Visa',
            'expires_at' => fake()->dateTimeBetween('+1 year', '+5 years'),
            'is_default' => false,
        ];
    }
}
