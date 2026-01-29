<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Payment\PaymentProviderEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentCustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'provider' => PaymentProviderEnum::FAKE,
            'provider_customer_id' => 'cus_'.fake()->unique()->lexify('??????????????'),
        ];
    }
}
