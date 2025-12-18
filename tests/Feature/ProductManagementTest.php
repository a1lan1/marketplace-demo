<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->seller = User::factory()->withSellerRole()->create();

    actingAs($this->seller);
});

test('a seller can create a product', function (): void {
    post('/products', [
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 9999,
        'stock' => 10,
    ])->assertRedirect('/products');

    assertDatabaseHas('products', ['name' => 'Test Product']);
});

test('a seller can update their product', function (): void {
    $product = Product::factory()->create(['user_id' => $this->seller->id]);

    put('/products/'.$product->id, [
        'name' => 'Updated Product Name',
    ])->assertRedirect('/products');

    assertDatabaseHas('products', ['name' => 'Updated Product Name']);
});

test('a seller can delete their product', function (): void {
    $product = Product::factory()->create(['user_id' => $this->seller->id]);

    delete('/products/'.$product->id)
        ->assertRedirect('/products');

    assertDatabaseMissing('products', ['id' => $product->id]);
});

test('a seller cannot update another sellers product', function (): void {
    $otherSeller = User::factory()->withSellerRole()->create();
    // Ensure other seller has the role
    $otherSellersProduct = Product::factory()->create(['user_id' => $otherSeller->id]);

    put('/products/'.$otherSellersProduct->id, [
        'name' => 'Updated by wrong user',
    ])->assertForbidden();
});
