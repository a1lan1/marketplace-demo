<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->seller = User::factory()->withSellerRole()->create();
    $this->seller->givePermissionTo(['products.view', 'products.create', 'products.edit-own', 'products.delete-own']);

    $this->otherUser = User::factory()->create();
});

// Catalog
test('guests and users can access the product catalog', function (): void {
    Product::factory(3)->create();
    get(route('products.catalog'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->component('Products/Catalog')->has('products.data', 3));
});

test('product catalog can be searched', function (): void {
    Product::factory()->create(['name' => 'My Awesome Phone']);
    Product::factory()->create(['name' => 'Some Other Gadget']);

    get(route('products.catalog', ['search' => 'Phone']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Products/Catalog')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'My Awesome Phone')
        );
});

// Show
test('anyone can view a single product', function (): void {
    $product = Product::factory()->create();
    get(route('products.show', $product))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->component('Products/Show')->has('product'));
});

// Index
test('a seller can view their own products list', function (): void {
    Product::factory(2)->create(['user_id' => $this->seller->id]);
    actingAs($this->seller)->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->component('Products/Index')->has('products.data', 2));
});

test('a user without permission cannot view products list', function (): void {
    actingAs($this->otherUser)->get(route('products.index'))->assertForbidden();
});

// Store
test('a seller can create a product', function (): void {
    $productData = [
        'name' => 'New Test Product',
        'description' => 'A great product.',
        'price' => 199.99,
        'stock' => 50,
        'cover_image' => UploadedFile::fake()->image('product.jpg'),
    ];

    actingAs($this->seller)
        ->post(route('products.store'), $productData)
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('products', ['name' => 'New Test Product', 'price' => '199.99']);
});

test('product creation requires valid data', function (array $badData, array|string $errors): void {
    actingAs($this->seller)->post(route('products.store'), $badData)->assertSessionHasErrors($errors);
})->with([
    'name is missing' => [['price' => 10], 'name'],
    'price is not numeric' => [['name' => 'Test', 'price' => 'abc'], 'price'],
    'stock is not an integer' => [['name' => 'Test', 'price' => 10, 'stock' => 'abc'], 'stock'],
]);

// Update
test('a seller can update their own product', function (): void {
    $product = Product::factory()->create(['user_id' => $this->seller->id]);

    actingAs($this->seller)
        ->put(route('products.update', $product), ['name' => 'Updated Name', 'price' => 123.45, 'stock' => 5])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Name', 'price' => '123.45']);
});

test('a seller cannot update another user product', function (): void {
    $product = Product::factory()->create(['user_id' => $this->otherUser->id]);
    actingAs($this->seller)
        ->put(route('products.update', $product), ['name' => 'Updated Name'])
        ->assertForbidden();
});

// Destroy
test('a seller can delete their own product', function (): void {
    $product = Product::factory()->create(['user_id' => $this->seller->id]);
    actingAs($this->seller)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');
    assertDatabaseMissing('products', ['id' => $product->id]);
});

test('a seller cannot delete another user product', function (): void {
    $product = Product::factory()->create(['user_id' => $this->otherUser->id]);
    actingAs($this->seller)
        ->delete(route('products.destroy', $product))
        ->assertForbidden();
});
