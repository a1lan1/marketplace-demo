<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Contracts\ProductSearcherInterface;
use App\Models\Product;
use Laravel\Scout\Builder as ScoutBuilder;

class ScoutProductSearcher implements ProductSearcherInterface
{
    public function search(string $query): ScoutBuilder
    {
        return Product::search($query);
    }
}
