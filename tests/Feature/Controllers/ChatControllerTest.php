<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->buyer = User::factory()->withBuyerRole()->create();
    $this->seller = User::factory()->withSellerRole()->create();

    $order = Order::factory()->create(['user_id' => $this->buyer->id]);
    $product = Product::factory()->create(['user_id' => $this->seller->id]);
    $order->products()->attach($product->id, ['quantity' => 1, 'price' => $product->price]);
});

test('a user can view the chat index page with their orders', function (string $role): void {
    $user = $this->{$role};

    actingAs($user)
        ->get(route('chat.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Chat')
            ->has('orders', 1)
        );
})->with(['buyer']);

test('a seller cannot view the chat index page', function (): void {
    actingAs($this->seller)
        ->get(route('chat.index'))
        ->assertForbidden();
});

test('a guest cannot view the chat index page', function (): void {
    get(route('chat.index'))->assertRedirect(route('login'));
});
