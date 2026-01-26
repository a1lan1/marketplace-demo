<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayoutMethodSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::role(RoleEnum::SELLER)->get();

        foreach ($sellers as $seller) {
            PayoutMethod::factory(rand(1, 2))
                ->for($seller, 'user')
                ->create();
        }
    }
}
