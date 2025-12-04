<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->withAvatar()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Admin',
                'email' => 'test@example.com',
            ])->assignRole(RoleEnum::ADMIN->value, RoleEnum::BUYER->value, RoleEnum::SELLER->value);

        User::factory()
            ->withAvatar()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Manager',
                'email' => 'demo@example.com',
            ])->assignRole(RoleEnum::MANAGER->value, RoleEnum::BUYER->value, RoleEnum::SELLER->value);

        User::factory()
            ->withAvatar()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Buyer',
                'email' => 'buyer@example.com',
            ])->assignRole(RoleEnum::BUYER->value, RoleEnum::SELLER->value);

        User::factory()
            ->withAvatar()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Seller',
                'email' => 'seller@example.com',
            ])->assignRole(RoleEnum::BUYER->value, RoleEnum::SELLER->value);

        User::factory(5)
            ->withAvatar()
            ->withoutTwoFactor()
            ->afterCreating(function (User $user): void {
                $user->assignRole(RoleEnum::BUYER->value, RoleEnum::SELLER->value);
            })
            ->create();
    }
}
