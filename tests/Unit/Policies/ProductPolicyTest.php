<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;

beforeEach(function (): void {
    $this->admin = User::factory()->withAdminRole()->create();
    $this->manager = User::factory()->withManagerRole()->create();

    // Seller with all permissions
    $this->seller = User::factory()->withSellerRole()->create();
    $this->seller->givePermissionTo(['products.view', 'products.create', 'products.edit-own', 'products.delete-own']);

    // Seller without edit/delete permissions
    $this->sellerWithoutEditDeletePermissions = User::factory()->create();
    $this->sellerWithoutEditDeletePermissions->givePermissionTo(['products.create', 'orders.view-own-seller']);

    $this->userWithViewCreate = User::factory()->create();
    $this->userWithViewCreate->givePermissionTo(['products.view', 'products.create']);

    $this->plainUser = User::factory()->create();

    $this->productOwnedBySeller = Product::factory()->create(['user_id' => $this->seller->id]);
    $this->productOwnedBySellerWithoutEditDeletePermissions = Product::factory()->create(['user_id' => $this->sellerWithoutEditDeletePermissions->id]);
    $this->otherProduct = Product::factory()->create(); // Owned by a random user
});

// viewAny
it('allows user with products.view permission to view any product', function (): void {
    expect((new ProductPolicy)->viewAny($this->userWithViewCreate))->toBeTrue();
});

it('denies user without products.view permission to view any product', function (): void {
    expect((new ProductPolicy)->viewAny($this->plainUser))->toBeFalse();
});

it('allows any user to view a specific product', function (): void {
    expect((new ProductPolicy)->view($this->plainUser, $this->otherProduct))->toBeTrue();
});

// create
it('allows user with products.create permission to create a product', function (): void {
    expect((new ProductPolicy)->create($this->userWithViewCreate))->toBeTrue();
});

it('denies user without products.create permission to create a product', function (): void {
    expect((new ProductPolicy)->create($this->plainUser))->toBeFalse();
});

// update
it('allows admin to update any product', function (): void {
    expect((new ProductPolicy)->update($this->admin, $this->otherProduct))->toBeTrue();
});

it('allows manager to update any product', function (): void {
    expect((new ProductPolicy)->update($this->manager, $this->otherProduct))->toBeTrue();
});

it('allows seller with products.edit-own permission to update their own product', function (): void {
    expect((new ProductPolicy)->update($this->seller, $this->productOwnedBySeller))->toBeTrue();
});

it('denies seller without products.edit-own permission to update their own product', function (): void {
    expect((new ProductPolicy)->update($this->sellerWithoutEditDeletePermissions, $this->productOwnedBySellerWithoutEditDeletePermissions))->toBeFalse();
});

it("denies seller from updating another user's product", function (): void {
    expect((new ProductPolicy)->update($this->seller, $this->otherProduct))->toBeFalse();
});

it('denies plain user from updating any product', function (): void {
    expect((new ProductPolicy)->update($this->plainUser, $this->otherProduct))->toBeFalse();
});

// delete
it('allows admin to delete any product', function (): void {
    expect((new ProductPolicy)->delete($this->admin, $this->otherProduct))->toBeTrue();
});

it('allows manager to delete any product', function (): void {
    expect((new ProductPolicy)->delete($this->manager, $this->otherProduct))->toBeTrue();
});

it('allows seller with products.delete-own permission to delete their own product', function (): void {
    expect((new ProductPolicy)->delete($this->seller, $this->productOwnedBySeller))->toBeTrue();
});

it('denies seller without products.delete-own permission to delete their own product', function (): void {
    expect((new ProductPolicy)->delete($this->sellerWithoutEditDeletePermissions, $this->productOwnedBySellerWithoutEditDeletePermissions))->toBeFalse();
});

it("denies seller from deleting another user's product", function (): void {
    expect((new ProductPolicy)->delete($this->seller, $this->otherProduct))->toBeFalse();
});

it('denies plain user from deleting any product', function (): void {
    expect((new ProductPolicy)->delete($this->plainUser, $this->otherProduct))->toBeFalse();
});
