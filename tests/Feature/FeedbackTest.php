<?php

declare(strict_types=1);

use App\Enums\Order\OrderStatusEnum;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

beforeEach(function (): void {
    $this->buyer = User::factory()->withBuyerRole()->create();
    $this->seller = User::factory()->withSellerRole()->create();
    $this->product = Product::factory()->create(['user_id' => $this->seller->id]);
});

test('authenticated user can post a feedback for a product', function (): void {
    $response = $this->actingAs($this->buyer)->postJson(route('api.feedbacks.store'), [
        'feedbackable_type' => 'product',
        'feedbackable_id' => $this->product->id,
        'rating' => 5,
        'comment' => 'This is a great product!',
    ]);

    $response->assertCreated()
        ->assertJsonPath('rating', 5)
        ->assertJsonPath('comment', 'This is a great product!');

    $this->assertDatabaseHas('feedbacks', [
        'feedbackable_id' => $this->product->id,
        'feedbackable_type' => Product::class,
        'user_id' => $this->buyer->id,
    ]);
});

test('feedback is marked as verified purchase if user bought the product', function (): void {
    // Create an order for the user and product
    Order::factory()
        ->for($this->buyer, 'buyer')
        ->hasAttached($this->product, ['quantity' => 1, 'price' => $this->product->price])
        ->create(['status' => OrderStatusEnum::COMPLETED]);

    $response = $this->actingAs($this->buyer)->postJson(route('api.feedbacks.store'), [
        'feedbackable_type' => 'product',
        'feedbackable_id' => $this->product->id,
        'rating' => 5,
        'comment' => 'Verified purchase review.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('is_verified_purchase', true);

    $this->assertDatabaseHas('feedbacks', [
        'feedbackable_id' => $this->product->id,
        'is_verified_purchase' => true,
    ]);
});

test('user can retrieve feedbacks for a product', function (): void {
    Feedback::factory()->count(3)->create([
        'feedbackable_id' => $this->product->id,
        'feedbackable_type' => Product::class,
    ]);

    $response = $this->getJson(route('api.feedbacks.index', ['type' => 'product', 'id' => $this->product->id]));

    $response->assertOk()
        ->assertJsonCount(3);
});
