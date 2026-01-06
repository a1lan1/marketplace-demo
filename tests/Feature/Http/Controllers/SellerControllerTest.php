<?php

use App\Http\Resources\SellerResource;
use App\Models\User;
use App\Services\SellerService;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;
use function Pest\Laravel\mock;

it('shows the seller page with data', function (): void {
    $seller = User::factory()->create();

    mock(SellerService::class, function ($mock) use ($seller): void {
        $mock->shouldReceive('getSellerWithProducts')
            ->withArgs(function ($user) use ($seller): bool {
                return $user->id === $seller->id;
            })
            ->once()
            ->andReturn($seller);
    });

    $expectedSellerData = SellerResource::make($seller)->resolve();
    // Adjust for JSON encoding behavior where 0.0 becomes 0
    if ($expectedSellerData['average_rating'] === 0.0) {
        $expectedSellerData['average_rating'] = 0;
    }

    get(route('sellers.show', $seller->id))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($expectedSellerData): void {
            $page->component('Seller/Show')
                ->where('seller', $expectedSellerData);
        });
});
