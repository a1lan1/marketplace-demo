<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Product;

use function Pest\Laravel\getJson;

test('autocomplete returns correct structure', function (): void {
    // Arrange
    $product = Product::factory()->create([
        'name' => 'Test Product',
    ]);

    // Act
    $response = getJson('/api/catalog/search?query=Test');

    // Assert
    $response->assertOk()
        ->assertJsonStructure([
            '*' => [
                'id',
                'name',
            ],
        ])
        ->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Test Product',
        ]);
});
