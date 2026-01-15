<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Contracts\Services\CurrencyServiceInterface;
use App\Enums\CacheKeyEnum;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

readonly class CachedCurrencyService implements CurrencyServiceInterface
{
    public function __construct(private CurrencyServiceInterface $service) {}

    /**
     * Get exchange rates for a given base currency.
     *
     * @return array{amount: float, base: string, date: string, rates: array<string, float>}
     *
     * @throws Exception
     */
    public function getRates(string $base = 'USD'): array
    {
        return Cache::tags(['currency'])->flexible(
            sprintf(CacheKeyEnum::CURRENCY_RATES->value, $base),
            [Date::now()->addHour(), Date::now()->addDay()],
            fn (): array => $this->service->getRates($base)
        );
    }
}
