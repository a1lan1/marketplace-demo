<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Location;
use App\Models\ResponseTemplate;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::role(RoleEnum::SELLER)->get();

        Location::factory($sellers->count() * 10)
            ->make()
            ->each(function (Location $location) use ($sellers): void {
                $location->seller_id = $sellers->random()->id;
                $location->save();

                Review::factory(rand(5, 20))
                    ->for($location)
                    ->create();
            });

        ResponseTemplate::factory($sellers->count() * 10)
            ->make()
            ->each(function (ResponseTemplate $responseTemplate) use ($sellers): void {
                $responseTemplate->seller_id = $sellers->random()->id;
                $responseTemplate->save();
            });
    }
}
