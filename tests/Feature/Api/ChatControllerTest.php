<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->admin = User::factory()->withAdminRole()->create();
    $this->buyer = User::factory()->withBuyerRole()->create();
    $this->seller = User::factory()->withSellerRole()->create();
    $this->randomUser = User::factory()->create();

    $this->order = Order::factory()->create(['user_id' => $this->buyer->id]);
    $product = Product::factory()->create(['user_id' => $this->seller->id]);
    $this->order->products()->attach($product->id, ['quantity' => 1, 'price' => $product->price->getAmount()]);
    Message::factory()->for($this->order)->for($this->buyer, 'user')->count(5)->create();
});

// getMessages
test('authorized users can get chat messages', function (string $role): void {
    $user = $this->{$role};

    actingAs($user, 'sanctum')
        ->getJson(route('api.orders.messages.index', $this->order))
        ->assertOk()
        ->assertJsonStructure(['data', 'total', 'per_page', 'current_page'])
        ->assertJsonCount(5, 'data');
})->with(['admin', 'buyer', 'seller']);

test('unauthorized user cannot get chat messages', function (): void {
    actingAs($this->randomUser, 'sanctum')
        ->getJson(route('api.orders.messages.index', $this->order))
        ->assertForbidden();
});

// store
test('authorized users can send a message', function (string $role): void {
    Event::fake();
    $user = $this->{$role};
    $messageContent = 'This is a test message from '.$role;

    actingAs($user, 'sanctum')
        ->postJson(route('api.chat.store', $this->order), ['message' => $messageContent])
        ->assertCreated()
        ->assertJson(['success' => true]);

    assertDatabaseHas('messages', [
        'order_id' => $this->order->id,
        'user_id' => $user->id,
        'message' => $messageContent,
    ]);

    Event::assertDispatched(MessageSent::class);
})->with(['admin', 'buyer', 'seller']);

test('unauthorized user cannot send a message', function (): void {
    actingAs($this->randomUser, 'sanctum')
        ->postJson(route('api.chat.store', $this->order), ['message' => 'test'])
        ->assertForbidden();
});

test('sending a message requires a message content', function (): void {
    actingAs($this->buyer, 'sanctum')
        ->postJson(route('api.chat.store', $this->order), ['message' => ''])
        ->assertJsonValidationErrors('message');
});
