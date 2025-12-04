<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::with('buyer')->get();
        $adminManagers = User::role([RoleEnum::ADMIN->value, RoleEnum::MANAGER->value])->get();

        foreach ($orders as $order) {
            /** @var User $buyer */
            $buyer = $order->buyer;

            /** @var User $interlocutor */
            $interlocutor = $adminManagers->random();

            $numberOfMessages = rand(5, 15);
            $currentSender = fake()->boolean() ? $buyer : $interlocutor;

            for ($i = 0; $i < $numberOfMessages; $i++) {
                Message::factory()->create([
                    'order_id' => $order->id,
                    'user_id' => $currentSender->id,
                    'message' => fake()->sentence(),
                ]);

                // Switch sender for the next message
                $currentSender = ($currentSender->id === $buyer->id) ? $interlocutor : $buyer;
            }
        }
    }
}
