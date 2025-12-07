<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Cknow\Money\Money;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->admin = User::factory()->withAdminRole()->create();
    $this->buyer = User::factory()->withBuyerRole()->create(['balance' => Money::USD(100000)]);
    $this->userWithoutRole = User::factory()->create();

    $this->product = Product::factory()->create(['price' => Money::USD(10000), 'stock' => 10]);
});

// Index
test('a buyer can view their orders', function (): void {
    Order::factory()->for($this->buyer, 'buyer')->count(2)->create();

    actingAs($this->buyer)
        ->get(route('orders.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Orders')
            ->has('orders.data', 2)
        );
});

test('a guest cannot view orders', function (): void {
    get(route('orders.index'))->assertRedirect(route('login'));
});

// Store
test('a buyer can place an order', function (): void {
    actingAs($this->buyer)->post(route('orders.store'), [
        'cart' => [['product_id' => $this->product->id, 'quantity' => 2]],
    ])->assertRedirect(route('orders.index'))->assertSessionHas('success');

    assertDatabaseHas('orders', [
        'user_id' => $this->buyer->id,
        'total_amount' => '200.00',
    ]);
    assertDatabaseHas('products', [
        'id' => $this->product->id,
        'stock' => 8, // 10 - 2
    ]);
    // Check buyer balance
    $this->buyer->refresh();
    expect((int) $this->buyer->balance->getAmount())->toBe(80000);
});

test('an order fails if stock is insufficient', function (): void {
    actingAs($this->buyer)->post(route('orders.store'), [
        'cart' => [['product_id' => $this->product->id, 'quantity' => 11]],
    ])->assertRedirect()->assertSessionHasErrors('purchase');

    expect($this->product->stock)->toBe(10);
});

test('an order fails if balance is insufficient', function (): void {
    $buyerWithLowBalance = User::factory()->withBuyerRole()->create(['balance' => Money::USD(100)]);

    actingAs($buyerWithLowBalance)->post(route('orders.store'), [
        'cart' => [['product_id' => $this->product->id, 'quantity' => 1]],
    ])->assertRedirect()->assertSessionHasErrors('purchase');
});

// Update Status
test('an admin can update order status', function (): void {
    $order = Order::factory()->for($this->buyer, 'buyer')->create(['status' => OrderStatusEnum::PENDING]);

    actingAs($this->admin)->put(route('orders.status.update', $order), [
        'status' => OrderStatusEnum::COMPLETED->value,
    ])->assertRedirect()->assertSessionHas('success');

    assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatusEnum::COMPLETED->value,
    ]);
});

test('a non-admin cannot update order status', function (): void {
    $order = Order::factory()->for($this->buyer, 'buyer')->create();

    actingAs($this->buyer)->put(route('orders.status.update', $order), [
        'status' => OrderStatusEnum::COMPLETED->value,
    ])->assertForbidden();
});
