<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role([RoleEnum::BUYER, RoleEnum::SELLER])->get();

        foreach ($users as $user) {
            PaymentMethod::factory(rand(2, 5))
                ->for($user)
                ->create();
        }
    }
}
