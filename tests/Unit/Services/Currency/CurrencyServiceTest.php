<?php

use App\Services\Currency\CurrencyService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('returns rates on successful response', function (): void {
    Http::fake([
        '*/rates*' => Http::response(['rates' => ['EUR' => 0.9, 'GBP' => 0.8]], 200),
    ]);

    $service = new CurrencyService('https://example.com', 5);
    $rates = $service->getRates('USD');

    expect($rates)->toBe(['rates' => ['EUR' => 0.9, 'GBP' => 0.8]]);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://example.com/rates?base=USD';
    });
});

it('throws exception on failed response', function (): void {
    Http::fake([
        '*/rates*' => Http::response('Error', 500),
    ]);

    $service = new CurrencyService('https://example.com', 5);
    $service->getRates('USD');
})->throws(Exception::class, 'Failed to fetch rates from currency service');
