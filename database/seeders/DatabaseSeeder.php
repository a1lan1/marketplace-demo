<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        if (! app()->environment('production')) {
            $this->call([
                UserSeeder::class,
                ProductSeeder::class,
                OrderSeeder::class,
                MessageSeeder::class,
                UserActivitySeeder::class,
                GeoSeeder::class,
                FeedbackSeeder::class,
                TransactionSeeder::class,
            ]);
        }
    }
}
