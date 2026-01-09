<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of Product
 *
 * @extends Builder<TModelClass>
 */
class ProductBuilder extends Builder
{
    public function recommended(array $productIds): self
    {
        if ($productIds === []) {
            return $this;
        }

        return $this
            ->whereIn('id', $productIds)
            ->orderByRaw('array_position(ARRAY['.implode(',', $productIds).']::bigint[], id::bigint)');
    }
}
