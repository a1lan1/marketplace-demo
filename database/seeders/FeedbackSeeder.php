<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $buyers = User::role(RoleEnum::BUYER)->get();
        $sellers = User::role(RoleEnum::SELLER)->get();

        foreach ($products as $product) {
            for ($i = 0; $i < rand(3, 10); $i++) {
                $buyer = $buyers->random();

                $exists = Feedback::where('user_id', $buyer->id)
                    ->where('feedbackable_id', $product->id)
                    ->where('feedbackable_type', Product::class)
                    ->exists();

                if (! $exists) {
                    Feedback::factory()->create([
                        'user_id' => $buyer->id,
                        'feedbackable_id' => $product->id,
                        'feedbackable_type' => Product::class,
                        'is_verified_purchase' => (bool) rand(0, 1),
                    ]);
                }
            }
        }

        foreach ($sellers as $seller) {
            for ($i = 0; $i < rand(3, 10); $i++) {
                $buyer = $buyers->random();

                $exists = Feedback::where('user_id', $buyer->id)
                    ->where('feedbackable_id', $seller->id)
                    ->where('feedbackable_type', User::class)
                    ->exists();

                if (! $exists) {
                    Feedback::factory()->create([
                        'user_id' => $buyer->id,
                        'feedbackable_id' => $seller->id,
                        'feedbackable_type' => User::class,
                    ]);
                }
            }

        }
    }
}
