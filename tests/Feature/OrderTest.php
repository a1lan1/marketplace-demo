<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Order\OrderStatusEnum;
use App\Enums\Payment\PaymentTypeEnum;
use App\Jobs\ProcessPayoutsJob;
use App\Models\Product;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

beforeEach(function (): void {
    Notification::fake();
    Bus::fake([ProcessPayoutsJob::class]);
    $this->buyer = User::factory()->withBuyerRole()->create(['balance' => Money::USD(100000)]);
    actingAs($this->buyer);
});

test('user can place order', function (): void {
    // Arrange
    $product = Product::factory()->create(['price' => Money::USD(10000), 'stock' => 10]);

    // Act
    $response = post(route('orders.store'), [
        'cart' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
        'payment_type' => PaymentTypeEnum::BALANCE->value,
    ], ['Idempotency-Key' => 'test-key']);

    // Assert
    $response->assertRedirect(route('orders.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('orders', [
        'user_id' => $this->buyer->id,
        'total_amount' => 20000,
        'status' => OrderStatusEnum::PAID->value,
    ]);
});

test('user cannot order non-existent product', function (): void {
    // Act
    $response = post(route('orders.store'), [
        'cart' => [
            ['product_id' => 99999, 'quantity' => 1],
        ],
        'payment_type' => PaymentTypeEnum::BALANCE->value,
    ], ['Idempotency-Key' => 'test-key']);

    // Assert
    $response->assertSessionHasErrors('cart.0.product_id');
});
