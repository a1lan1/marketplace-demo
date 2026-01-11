<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Contracts\Services\CurrencyServiceInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService implements CurrencyServiceInterface
{
    public function __construct(protected string $baseUrl, protected int $timeout) {}

    /**
     * Get exchange rates for a given base currency.
     *
     * @return array{amount: float, base: string, date: string, rates: array<string, float>}
     *
     * @throws Exception
     */
    public function getRates(string $base = 'USD'): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->get('rates', ['base' => $base]);

            if (! $response->successful()) {
                Log::error('Currency service request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('Failed to fetch rates from currency service');
            }

            return $response->json();
        } catch (Exception $exception) {
            Log::error('Could not connect to Currency service.', [
                'exception' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }
}
