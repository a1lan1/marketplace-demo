<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Purchase;

use App\DTO\CartItemDTO;
use App\Exceptions\NotEnoughStockException;
use App\Models\Product;
use App\Services\Purchase\InventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\LaravelData\DataCollection;

test('ensure stock throws exception if product not found', function (): void {
    $service = new InventoryService;
    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO(999, 1),
    ]);

    $service->ensureStock($cart, collect([]));
})->throws(ModelNotFoundException::class);

test('ensure stock throws exception if requested quantity exceeds stock', function (): void {
    $product = Product::factory()->create(['stock' => 5]);
    $service = new InventoryService;
    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO($product->id, 10),
    ]);
    $products = collect([$product->id => $product]);

    $service->ensureStock($cart, $products);
})->throws(NotEnoughStockException::class);

test('ensure stock passes if sufficient stock', function (): void {
    $product = Product::factory()->create(['stock' => 10]);
    $service = new InventoryService;
    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO($product->id, 5),
    ]);
    $products = collect([$product->id => $product]);

    $service->ensureStock($cart, $products);
    expect(true)->toBeTrue();
});

test('decrement stock reduces product stock', function (): void {
    $product = Product::factory()->create(['stock' => 10]);
    $service = new InventoryService;
    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO($product->id, 3),
    ]);
    $products = collect([$product->id => $product]);

    $service->decrementStock($cart, $products);

    expect($product->fresh()->stock)->toBe(7);
});
