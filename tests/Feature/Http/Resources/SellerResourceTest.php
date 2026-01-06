<?php

use App\Http\Resources\SellerResource;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

it('formats seller data correctly', function (): void {
    $seller = User::factory()->create();
    Product::factory()->count(2)->create(['user_id' => $seller->id]);
    Feedback::factory()->create(['feedbackable_id' => $seller->id, 'feedbackable_type' => User::class, 'rating' => 4]);
    Feedback::factory()->create(['feedbackable_id' => $seller->id, 'feedbackable_type' => User::class, 'rating' => 5]);

    $seller->load('products');

    $resource = new SellerResource($seller);
    $request = new Request;
    $result = $resource->toArray($request);

    expect($result['id'])->toBe($seller->id)
        ->and($result['name'])->toBe($seller->name)
        ->and($result['average_rating'])->toBe(4.5)
        ->and($result['reviews_count'])->toBe(2)
        ->and(count($result['products']))->toBe(2);
});
