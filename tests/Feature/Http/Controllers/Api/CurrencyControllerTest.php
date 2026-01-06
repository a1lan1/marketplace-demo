<?php

use App\Contracts\Services\CurrencyServiceInterface;

use function Pest\Laravel\getJson;
use function Pest\Laravel\mock;

it('returns rates on success', function (): void {
    $rates = ['rates' => ['EUR' => 0.9]];
    mock(CurrencyServiceInterface::class, function ($mock) use ($rates): void {
        $mock->shouldReceive('getRates')->with('USD')->once()->andReturn($rates);
    });

    getJson(route('currency.rates'))
        ->assertOk()
        ->assertJson($rates);
});

it('returns error on failure', function (): void {
    mock(CurrencyServiceInterface::class, function ($mock): void {
        $mock->shouldReceive('getRates')->with('USD')->once()->andThrow(new Exception);
    });

    getJson(route('currency.rates'))
        ->assertStatus(503)
        ->assertJson(['error' => 'Failed to fetch rates']);
});
