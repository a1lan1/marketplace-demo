<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

test('catalog page is accessible to guests and users', function (?User $user): void {
    // Arrange
    $actingAs = $user instanceof User ? actingAs($user) : $this;

    // Act
    $response = $actingAs->get(route('products.catalog'));

    // Assert
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->component('Products/Catalog'));
})->with([
    'guest' => fn (): null => null,
    'user' => fn () => User::factory()->create(),
]);

test('product show page is accessible to guests and users', function (?User $user): void {
    // Arrange
    $product = Product::factory()->create();
    $actingAs = $user instanceof User ? actingAs($user) : $this;

    // Act
    $response = $actingAs->get(route('products.show', $product));

    // Assert
    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Products/Show')
            ->has('product')
        );
})->with([
    'guest' => fn (): null => null,
    'user' => fn () => User::factory()->create(),
]);
