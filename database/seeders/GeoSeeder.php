<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Location;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::role(RoleEnum::SELLER)->get();

        Location::factory(rand(50, 100))
            ->for($sellers->random(), 'seller')
            ->create()
            ->each(function (Location $location): void {
                Review::factory(rand(5, 20))
                    ->for($location)
                    ->create();
            });
    }
}
