<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->withAvatar()
            ->withBaseRoles()
            ->withAdminRole()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Admin',
                'email' => 'test@example.com',
            ]);

        User::factory()
            ->withAvatar()
            ->withBaseRoles()
            ->withManagerRole()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Manager',
                'email' => 'demo@example.com',
            ]);

        User::factory()
            ->withAvatar()
            ->withBaseRoles()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Buyer',
                'email' => 'buyer@example.com',
            ]);

        User::factory()
            ->withAvatar()
            ->withBaseRoles()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Seller',
                'email' => 'seller@example.com',
            ]);

        User::factory(5)
            ->withAvatar()
            ->withBaseRoles()
            ->withoutTwoFactor()
            ->create();
    }
}
