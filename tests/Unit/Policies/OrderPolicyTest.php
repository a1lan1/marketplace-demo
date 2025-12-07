<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\OrderPolicy;

beforeEach(function (): void {
    $this->admin = User::factory()->withAdminRole()->create();
    $this->manager = User::factory()->withManagerRole()->create();
    $this->buyer = User::factory()->withBuyerRole()->create();
    $this->seller = User::factory()->withSellerRole()->create();
    $this->plainUser = User::factory()->create();

    $this->order = Order::factory()->create(['user_id' => $this->buyer->id]);
    $product = Product::factory()->create(['user_id' => $this->seller->id]);
    $this->order->products()->attach($product->id, ['quantity' => 1, 'price' => $product->price]);

    $this->otherOrder = Order::factory()->create();
});

// viewAny
it('allows buyer to view any order list', function (): void {
    expect((new OrderPolicy)->viewAny($this->buyer))->toBeTrue();
});

it('allows admin to view any order list', function (): void {
    expect((new OrderPolicy)->viewAny($this->admin))->toBeTrue();
});

it('allows manager to view any order list', function (): void {
    expect((new OrderPolicy)->viewAny($this->manager))->toBeTrue();
});

it('denies plain user from viewing any order list', function (): void {
    expect((new OrderPolicy)->viewAny($this->plainUser))->toBeFalse();
});

// view
it('allows buyer to view their own specific order', function (): void {
    expect((new OrderPolicy)->view($this->buyer, $this->order))->toBeTrue();
});

it('allows admin to view a specific order', function (): void {
    expect((new OrderPolicy)->view($this->admin, $this->otherOrder))->toBeTrue();
});

it('allows manager to view a specific order', function (): void {
    expect((new OrderPolicy)->view($this->manager, $this->otherOrder))->toBeTrue();
});

it('denies seller from viewing an order they are not the buyer of', function (): void {
    expect((new OrderPolicy)->view($this->seller, $this->order))->toBeFalse();
});

it('denies plain user from viewing any specific order', function (): void {
    expect((new OrderPolicy)->view($this->plainUser, $this->order))->toBeFalse();
});

// create
it('allows buyer to create an order', function (): void {
    expect((new OrderPolicy)->create($this->buyer))->toBeTrue();
});

it('allows admin to create an order', function (): void {
    expect((new OrderPolicy)->create($this->admin))->toBeTrue();
});

it('denies plain user from creating an order', function (): void {
    expect((new OrderPolicy)->create($this->plainUser))->toBeFalse();
});

// update
it('allows admin to update an order', function (): void {
    expect((new OrderPolicy)->update($this->admin))->toBeTrue();
});

it('allows manager to update an order', function (): void {
    expect((new OrderPolicy)->update($this->manager))->toBeTrue();
});

it('denies plain user from updating an order', function (): void {
    expect((new OrderPolicy)->update($this->plainUser))->toBeFalse();
});

// delete, restore, forceDelete
it('allows admin to delete an order', function (): void {
    expect((new OrderPolicy)->delete($this->admin))->toBeTrue();
});

it('denies manager from deleting an order', function (): void {
    expect((new OrderPolicy)->delete($this->manager))->toBeFalse();
});

it('denies plain user from deleting an order', function (): void {
    expect((new OrderPolicy)->delete($this->plainUser))->toBeFalse();
});

it('allows admin to restore an order', function (): void {
    expect((new OrderPolicy)->restore($this->admin))->toBeTrue();
});

it('denies manager from restoring an order', function (): void {
    expect((new OrderPolicy)->restore($this->manager))->toBeFalse();
});

it('allows admin to force delete an order', function (): void {
    expect((new OrderPolicy)->forceDelete($this->admin))->toBeTrue();
});

it('denies manager from force deleting an order', function (): void {
    expect((new OrderPolicy)->forceDelete($this->manager))->toBeFalse();
});

// viewChat & sendMessage
it('allows buyer to view chat and send message for their order', function (): void {
    expect((new OrderPolicy)->viewChat($this->buyer, $this->order))->toBeTrue();
    expect((new OrderPolicy)->sendMessage($this->buyer, $this->order))->toBeTrue();
});

it('allows seller to view chat and send message for an order containing their product', function (): void {
    expect((new OrderPolicy)->viewChat($this->seller, $this->order))->toBeTrue();
    expect((new OrderPolicy)->sendMessage($this->seller, $this->order))->toBeTrue();
});

it('allows admin to view chat and send message for any order', function (): void {
    expect((new OrderPolicy)->viewChat($this->admin, $this->otherOrder))->toBeTrue();
    expect((new OrderPolicy)->sendMessage($this->admin, $this->otherOrder))->toBeTrue();
});

it('denies plain user from viewing chat or sending message for any order', function (): void {
    expect((new OrderPolicy)->viewChat($this->plainUser, $this->order))->toBeFalse();
    expect((new OrderPolicy)->sendMessage($this->plainUser, $this->order))->toBeFalse();
});
