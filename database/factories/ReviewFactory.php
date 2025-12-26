<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use App\Models\Location;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'source' => fake()->randomElement(ReviewSourceEnum::cases()),
            'external_id' => fake()->unique()->uuid,
            'author_name' => fake()->name,
            'text' => fake()->paragraph,
            'rating' => fake()->numberBetween(1, 5),
            'sentiment' => fake()->randomElement(SentimentEnum::cases()),
            'published_at' => fake()->dateTimeThisYear,
        ];
    }
}
