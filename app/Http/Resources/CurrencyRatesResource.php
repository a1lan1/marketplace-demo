<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property array{amount?: float, base?: string, date?: string, rates?: array<string, float>} $resource
 */
class CurrencyRatesResource extends JsonResource
{
    /**
     * @return array{
     *     amount: float,
     *     base: string,
     *     date: string,
     *     rates: array<string, float>
     * }
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'amount' => (float) ($this->resource['amount'] ?? 1.0),
            'base' => (string) ($this->resource['base'] ?? 'USD'),
            'date' => (string) ($this->resource['date'] ?? now()->toDateString()),
            'rates' => (array) ($this->resource['rates'] ?? []),
        ];
    }
}
