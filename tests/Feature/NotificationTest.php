<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Order\OrderCreated;
use App\Events\Order\OrderStatusChanged;
use App\Events\Payment\PaymentProcessingFailed;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Order\OrderConfirmationNotification;
use App\Notifications\Order\OrderStatusUpdatedNotification;
use App\Notifications\Payment\PaymentFailedNotification;
use App\Notifications\Seller\NewSaleNotification;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();
});

test('sends order status updated notification to buyer', function (): void {
    // Arrange
    $buyer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $buyer->id]);

    // Act
    event(new OrderStatusChanged($order));

    // Assert
    Notification::assertSentTo(
        $buyer,
        OrderStatusUpdatedNotification::class,
        function (OrderStatusUpdatedNotification $notification) use ($order): bool {
            return $notification->order->id === $order->id;
        }
    );
});

test('sends order confirmation notification to buyer', function (): void {
    // Arrange
    $buyer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $buyer->id]);

    // Act
    event(new OrderCreated($order, new Collection));

    // Assert
    Notification::assertSentTo(
        $buyer,
        OrderConfirmationNotification::class,
        function (OrderConfirmationNotification $notification) use ($order): bool {
            return $notification->order->id === $order->id;
        }
    );
});

test('sends new sale notification to seller', function (): void {
    // Arrange
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['user_id' => $seller->id]);
    $order = Order::factory()->create(['user_id' => $buyer->id]);
    $order->products()->attach($product->id, ['quantity' => 1, 'price' => $product->price->getAmount()]);

    $sellerPayouts = new Collection([
        $seller->id => Money::USD(1000),
    ]);

    // Act
    event(new OrderCreated($order, $sellerPayouts));

    // Assert
    Notification::assertSentTo(
        $seller,
        NewSaleNotification::class,
        function (NewSaleNotification $notification) use ($order): bool {
            return $notification->order->id === $order->id;
        }
    );

    Notification::assertNotSentTo($buyer, NewSaleNotification::class);
});

test('sends payment failed notification to buyer', function (): void {
    // Arrange
    $buyer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $buyer->id]);
    $payment = Payment::factory()->create(['order_id' => $order->id, 'user_id' => $buyer->id]);
    $errorMessage = 'Your card was declined.';

    // Act
    event(new PaymentProcessingFailed($payment, $buyer, $errorMessage));

    // Assert
    Notification::assertSentTo(
        $buyer,
        PaymentFailedNotification::class,
        function (PaymentFailedNotification $notification) use ($errorMessage): bool {
            return $notification->errorMessage === $errorMessage;
        }
    );
});
