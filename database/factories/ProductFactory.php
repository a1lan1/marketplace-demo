<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MediaCollection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(1000, 100000),
            'stock' => fake()->numberBetween(0, 100),
        ];
    }

    public function withCoverImage(): static
    {
        return $this->afterCreating(function (Product $product): void {
            $product->addMediaFromUrl('https://picsum.photos/600')
                ->toMediaCollection(MediaCollection::ProductCoverImage->value);
        });
    }
}
