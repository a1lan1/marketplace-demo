<?php

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\SellerService;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->service = new SellerService($this->userRepository);
});

it('returns seller with products and caches result', function (): void {
    $seller = User::factory()->make(['id' => 1]);
    $sellerWithProducts = User::factory()->make(['id' => 1]);

    $this->userRepository->shouldReceive('getSellerWithProducts')
        ->once()
        ->with($seller)
        ->andReturn($sellerWithProducts);

    $cacheSpy = Cache::spy();

    $result = $this->service->getSellerWithProducts($seller);

    expect($result)->toBe($sellerWithProducts);

    $cacheSpy->shouldHaveReceived('tags')->with(['products', 'sellers'])->once();
});
