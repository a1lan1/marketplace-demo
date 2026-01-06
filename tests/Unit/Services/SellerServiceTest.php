<?php

use App\Models\Product;
use App\Models\User;
use App\Services\SellerService;
use Illuminate\Support\Facades\Cache;

it('returns seller with products and caches result', function (): void {
    $seller = User::factory()->create();
    Product::factory()->count(10)->create(['user_id' => $seller->id]);

    $service = new SellerService;

    $cacheSpy = Cache::spy();

    $result = $service->getSellerWithProducts($seller);

    expect($result->relationLoaded('products'))->toBeTrue()
        ->and($result->products)->toHaveCount(8);

    $cacheSpy->shouldHaveReceived('tags')->with(['products', 'sellers'])->once();
});
