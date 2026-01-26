<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Geo\LocationTypeEnum;
use App\Models\Location;
use App\Models\User;
use App\ValueObjects\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'seller_id' => User::factory()->withSellerRole(),
            'name' => fake()->company,
            'type' => fake()->randomElement(LocationTypeEnum::cases()),
            'address' => new Address(
                country: fake()->countryCode,
                city: fake()->city,
                street: fake()->streetName,
                houseNumber: fake()->buildingNumber,
                postalCode: fake()->postcode,
                fullAddress: fake()->address,
            ),
            'latitude' => fake()->latitude,
            'longitude' => fake()->longitude,
            'external_ids' => ['google' => fake()->uuid],
        ];
    }
}
