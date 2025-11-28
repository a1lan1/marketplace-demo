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
            ->withoutTwoFactor()
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        User::factory()
            ->withAvatar()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
            ]);

        User::factory(5)
            ->withAvatar()
            ->withoutTwoFactor()
            ->create();
    }
}
