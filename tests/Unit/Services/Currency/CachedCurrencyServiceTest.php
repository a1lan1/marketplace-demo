<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Currency;

use App\Contracts\Services\CurrencyServiceInterface;
use App\Enums\CacheKeyEnum;
use App\Services\Currency\CachedCurrencyService;
use Illuminate\Support\Facades\Cache;
use Mockery;

test('get rates returns cached result', function (): void {
    // Arrange
    $innerService = Mockery::mock(CurrencyServiceInterface::class);
    $base = 'USD';
    $rates = ['rates' => ['EUR' => 0.9]];

    Cache::shouldReceive('tags')->with(['currency'])->andReturnSelf();
    Cache::shouldReceive('flexible')
        ->once()
        ->with(
            sprintf(CacheKeyEnum::CURRENCY_RATES->value, $base),
            Mockery::type('array'),
            Mockery::type('closure')
        )
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $innerService->shouldReceive('getRates')
        ->once()
        ->with($base)
        ->andReturn($rates);

    $cachedService = new CachedCurrencyService($innerService);

    // Act
    $result = $cachedService->getRates($base);

    // Assert
    expect($result)->toBe($rates);
});
