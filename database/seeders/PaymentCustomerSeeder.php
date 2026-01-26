<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\PaymentCustomer;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role([RoleEnum::BUYER, RoleEnum::SELLER])->get();

        foreach ($users as $user) {
            PaymentCustomer::factory()
                ->for($user)
                ->create();
        }
    }
}
