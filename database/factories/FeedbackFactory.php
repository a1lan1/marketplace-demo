<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'feedbackable_id' => Product::factory(),
            'feedbackable_type' => Product::class,
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph,
            'is_verified_purchase' => fake()->boolean,
        ];
    }
}
