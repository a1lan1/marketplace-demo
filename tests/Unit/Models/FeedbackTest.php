<?php

use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;

it('can filter feedbacks for a specific entity', function (): void {
    $product = Product::factory()->create();
    $seller = User::factory()->create();

    $feedbackForProduct = Feedback::factory()->for($product, 'feedbackable')->create();
    $feedbackForSeller = Feedback::factory()->for($seller, 'feedbackable')->create();

    $this->assertCount(1, Feedback::forEntity(Product::class, $product->id)->get());
    $this->assertCount(1, Feedback::forEntity(User::class, $seller->id)->get());

    expect(Feedback::forEntity(Product::class, $product->id)->first()->id)->toBe($feedbackForProduct->id)
        ->and(Feedback::forEntity(User::class, $seller->id)->first()->id)->toBe($feedbackForSeller->id);
});

it('can filter feedbacks for a user (seller and their products)', function (): void {
    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();

    $product = Product::factory()->for($seller, 'seller')->create();
    $otherProduct = Product::factory()->for($otherSeller, 'seller')->create();

    // Feedback on the seller themselves
    Feedback::factory()->for($seller, 'feedbackable')->create();
    // Feedback on one of the seller's products
    Feedback::factory()->for($product, 'feedbackable')->create();

    // Feedback that should NOT be found
    Feedback::factory()->for($otherSeller, 'feedbackable')->create();
    Feedback::factory()->for($otherProduct, 'feedbackable')->create();

    $this->assertCount(2, Feedback::forUser($seller->id)->get());
});

it('can eager load author details', function (): void {
    $feedback = Feedback::factory()->create();

    $result = Feedback::withAuthorDetails()->find($feedback->id);

    expect($result->relationLoaded('author'))->toBeTrue()
        ->and($result->author)->toHaveKeys(['id', 'name'])
        ->and($result->author->relationLoaded('media'))->toBeTrue();
});

it('can lazy load author details', function (): void {
    $feedback = Feedback::factory()->create();

    $feedback->loadAuthorDetails();

    expect($feedback->relationLoaded('author'))->toBeTrue()
        ->and($feedback->author)->toHaveKeys(['id', 'name'])
        ->and($feedback->author->relationLoaded('media'))->toBeTrue();
});
