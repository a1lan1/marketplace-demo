<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;

beforeEach(function (): void {
    $this->admin = User::factory()->withAdminRole()->create();
    $this->manager = User::factory()->withManagerRole()->create();

    $this->seller = User::factory()->withSellerRole()->create();
    $this->seller->givePermissionTo(['products.view', 'products.create', 'products.edit-own', 'products.delete-own']);

    $this->user = User::factory()->create();

    $this->product = Product::factory()->create(['user_id' => $this->seller->id]);
    $this->otherProduct = Product::factory()->create();

    $this->policy = new ProductPolicy;
});

// viewAny
test('user with permission can view any product', function (): void {
    $this->user->givePermissionTo('products.view');
    expect($this->policy->viewAny($this->user))->toBeTrue();
});

test('user without permission cannot view any product', function (): void {
    $this->user->revokePermissionTo('products.view');
    expect($this->policy->viewAny($this->user))->toBeFalse();
});

// create
test('user with permission can create product', function (): void {
    $this->user->givePermissionTo('products.create');
    expect($this->policy->create($this->user))->toBeTrue();
});

test('user without permission cannot create product', function (): void {
    $this->user->revokePermissionTo('products.create');
    expect($this->policy->create($this->user))->toBeFalse();
});

// update
test('admin can update any product', function (): void {
    expect($this->policy->update($this->admin, $this->otherProduct))->toBeTrue();
});

test('manager can update any product', function (): void {
    expect($this->policy->update($this->manager, $this->otherProduct))->toBeTrue();
});

test('seller with permission can update own product', function (): void {
    expect($this->policy->update($this->seller, $this->product))->toBeTrue();
});

test('seller without permission cannot update own product', function (): void {
    $sellerWithoutEditPermission = User::factory()->create();
    $sellerWithoutEditPermission->givePermissionTo(['products.create', 'products.delete-own']);

    $productOfSellerWithoutPermission = Product::factory()->create(['user_id' => $sellerWithoutEditPermission->id]);

    expect($this->policy->update($sellerWithoutEditPermission, $productOfSellerWithoutPermission))->toBeFalse();
});

test('seller cannot update another user product', function (): void {
    expect($this->policy->update($this->seller, $this->otherProduct))->toBeFalse();
});

test('user cannot update product', function (): void {
    expect($this->policy->update($this->user, $this->product))->toBeFalse();
});

// delete
test('admin can delete any product', function (): void {
    expect($this->policy->delete($this->admin, $this->otherProduct))->toBeTrue();
});

test('manager can delete any product', function (): void {
    expect($this->policy->delete($this->manager, $this->otherProduct))->toBeTrue();
});

test('seller with permission can delete own product', function (): void {
    expect($this->policy->delete($this->seller, $this->product))->toBeTrue();
});

test('seller without permission cannot delete own product', function (): void {
    $sellerWithoutDeletePermission = User::factory()->create();
    $sellerWithoutDeletePermission->givePermissionTo(['products.create', 'products.edit-own']);

    $productOfSellerWithoutPermission = Product::factory()->create(['user_id' => $sellerWithoutDeletePermission->id]);

    expect($this->policy->delete($sellerWithoutDeletePermission, $productOfSellerWithoutPermission))->toBeFalse();
});

test('seller cannot delete other product', function (): void {
    expect($this->policy->delete($this->seller, $this->otherProduct))->toBeFalse();
});

test('user cannot delete product', function (): void {
    expect($this->policy->delete($this->user, $this->product))->toBeFalse();
});
