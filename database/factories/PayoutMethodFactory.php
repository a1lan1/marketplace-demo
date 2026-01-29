<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentTypeEnum;
use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayoutMethod>
 */
class PayoutMethodFactory extends Factory
{
    protected $model = PayoutMethod::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'provider' => fake()->randomElement(PaymentProviderEnum::cases())->value,
            'provider_id' => fake()->uuid,
            'type' => fake()->randomElement(PaymentTypeEnum::cases()),
            'details' => [
                'amount' => round(fake()->numberBetween(1, 1000) / 100, 2),
                'currency' => fake()->currencyCode,
            ],
        ];
    }
}
