<?php

declare(strict_types=1);

namespace App\Services\Purchase;

use App\Models\Product;
use Cknow\Money\Money;
use Illuminate\Support\Collection;

readonly class CalculationResult
{
    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Money>  $sellerPayouts
     */
    public function __construct(
        public Money $totalAmount,
        public Collection $sellerPayouts,
        public Collection $products
    ) {}
}
