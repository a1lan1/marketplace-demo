<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\OrderPolicy;

beforeEach(function (): void {
    $this->admin = User::factory()->withAdminRole()->create();
    $this->manager = User::factory()->withManagerRole()->create();
    $this->buyer = User::factory()->withBuyerRole()->create();
    $this->seller = User::factory()->withSellerRole()->create();
    $this->user = User::factory()->create();

    $this->order = Order::factory()->create(['user_id' => $this->buyer->id]);
    $product = Product::factory()->create(['user_id' => $this->seller->id]);
    $this->order->products()->attach($product->id, ['quantity' => 1, 'price' => $product->price]);

    $this->otherOrder = Order::factory()->create();

    $this->policy = new OrderPolicy;
});

// viewAny
test('buyer can view any order', function (): void {
    expect($this->policy->viewAny($this->buyer))->toBeTrue();
});

test('admin can view any order', function (): void {
    expect($this->policy->viewAny($this->admin))->toBeTrue();
});

test('manager can view any order', function (): void {
    expect($this->policy->viewAny($this->manager))->toBeTrue();
});

test('user cannot view any order', function (): void {
    expect($this->policy->viewAny($this->user))->toBeFalse();
});

// view
test('buyer can view their own order', function (): void {
    expect($this->policy->view($this->buyer, $this->order))->toBeTrue();
});

test('seller can view order containing their product', function (): void {
    expect($this->policy->view($this->seller, $this->order))->toBeTrue();
});

test('admin can view other order', function (): void {
    expect($this->policy->view($this->admin, $this->otherOrder))->toBeTrue();
});

test('user cannot view other order', function (): void {
    expect($this->policy->view($this->user, $this->order))->toBeFalse();
});

// create
test('buyer can create order', function (): void {
    expect($this->policy->create($this->buyer))->toBeTrue();
});

test('admin can create order', function (): void {
    expect($this->policy->create($this->admin))->toBeTrue();
});

test('user cannot create order', function (): void {
    expect($this->policy->create($this->user))->toBeFalse();
});

// update
test('admin can update order', function (): void {
    expect($this->policy->update($this->admin))->toBeTrue();
});

test('manager can update order', function (): void {
    expect($this->policy->update($this->manager))->toBeTrue();
});

test('user cannot update order', function (): void {
    expect($this->policy->update($this->user))->toBeFalse();
});

// delete, restore, forceDelete
test('admin can delete order', function (): void {
    expect($this->policy->delete($this->admin))->toBeTrue()
        ->and($this->policy->delete($this->manager))->toBeFalse()
        ->and($this->policy->delete($this->user))->toBeFalse();
});

test('admin can restore order', function (): void {
    expect($this->policy->restore($this->admin))->toBeTrue()
        ->and($this->policy->restore($this->manager))->toBeFalse()
        ->and($this->policy->restore($this->user))->toBeFalse();
});

test('admin can force delete order', function (): void {
    expect($this->policy->forceDelete($this->admin))->toBeTrue()
        ->and($this->policy->forceDelete($this->manager))->toBeFalse()
        ->and($this->policy->forceDelete($this->user))->toBeFalse();
});

// viewChat & sendMessage
test('buyer can view chat and send message in their order', function (): void {
    expect($this->policy->viewChat($this->buyer, $this->order))->toBeTrue()
        ->and($this->policy->sendMessage($this->buyer, $this->order))->toBeTrue();
});

test('seller can view chat and send message in order with their product', function (): void {
    expect($this->policy->viewChat($this->seller, $this->order))->toBeTrue()
        ->and($this->policy->sendMessage($this->seller, $this->order))->toBeTrue();
});

test('admin can view chat and send message in any order', function (): void {
    expect($this->policy->viewChat($this->admin, $this->otherOrder))->toBeTrue()
        ->and($this->policy->sendMessage($this->admin, $this->otherOrder))->toBeTrue();
});

test('user cannot view chat or send message', function (): void {
    expect($this->policy->viewChat($this->user, $this->order))->toBeFalse()
        ->and($this->policy->sendMessage($this->user, $this->order))->toBeFalse();
});
