<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ResponseTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResponseTemplate>
 */
class ResponseTemplateFactory extends Factory
{
    protected $model = ResponseTemplate::class;

    public function definition(): array
    {
        return [
            'seller_id' => User::factory()->withSellerRole(),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph,
        ];
    }
}
