<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Purchase;

use App\DTO\CartItemDTO;
use App\Models\Product;
use App\Services\Purchase\CalculationResult;
use App\Services\Purchase\CartCalculator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\LaravelData\DataCollection;

test('calculate returns correct total amount and payouts', function (): void {
    // Arrange
    $seller10 = (object) ['id' => 10];
    $seller20 = (object) ['id' => 20];

    $product1 = Product::factory()->make(['id' => 1, 'price' => 10000, 'user_id' => 10]);
    $product1->setRelation('seller', $seller10);

    $product2 = Product::factory()->make(['id' => 2, 'price' => 5000, 'user_id' => 10]);
    $product2->setRelation('seller', $seller10);

    $product3 = Product::factory()->make(['id' => 3, 'price' => 2000, 'user_id' => 20]);
    $product3->setRelation('seller', $seller20);

    $products = collect([
        1 => $product1,
        2 => $product2,
        3 => $product3,
    ]);

    $cartItems = new DataCollection(CartItemDTO::class, [
        new CartItemDTO(1, 2), // 2 * 10000 = 20000
        new CartItemDTO(2, 1), // 1 * 5000 = 5000
        new CartItemDTO(3, 5), // 5 * 2000 = 10000
    ]);

    $calculator = new CartCalculator;

    // Act
    $result = $calculator->calculate($cartItems, $products);

    // Assert
    // Total: 20000 + 5000 + 10000 = 35000
    expect($result->totalAmount->getAmount())->toBe('35000');

    // Payouts
    // Seller 10: 20000 + 5000 = 25000
    expect($result->sellerPayouts->get(10)->getAmount())->toBe('25000');
    // Seller 20: 10000
    expect($result->sellerPayouts->get(20)->getAmount())->toBe('10000');
});

test('calculate throws exception if product not found in collection', function (): void {
    // Arrange
    $cartItems = new DataCollection(CartItemDTO::class, [
        new CartItemDTO(999, 1), // Product 999 does not exist in the products collection
    ]);

    $products = collect([]); // Empty product collection

    $calculator = new CartCalculator;

    // Act & Assert
    expect(fn (): CalculationResult => $calculator->calculate($cartItems, $products))
        ->toThrow(ModelNotFoundException::class);
});
