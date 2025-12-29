<?php

declare(strict_types=1);

namespace App\Services\Purchase;

use App\Exceptions\NotEnoughStockException;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

class InventoryService
{
    /**
     * @throws NotEnoughStockException
     */
    public function ensureStock(DataCollection $cart, Collection $products): void
    {
        foreach ($cart as $item) {
            /** @var Product|null $product */
            $product = $products->get($item->productId);

            if (! $product) {
                throw new ModelNotFoundException(sprintf('Product with ID %s not found in the provided collection.', $item->productId));
            }

            if ($product->stock < $item->quantity) {
                throw new NotEnoughStockException($product, $item->quantity);
            }
        }
    }

    public function decrementStock(DataCollection $cart, Collection $products): void
    {
        foreach ($cart as $item) {
            /** @var Product|null $product */
            $product = $products->get($item->productId);

            $product?->decrement('stock', $item->quantity);
        }
    }
}
