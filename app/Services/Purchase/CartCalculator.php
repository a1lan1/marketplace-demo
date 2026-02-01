<?php

declare(strict_types=1);

namespace App\Services\Purchase;

use App\Models\Product;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

class CartCalculator
{
    /**
     * @param  Collection<int, Product>  $products
     */
    public function calculate(DataCollection $cart, Collection $products): CalculationResult
    {
        $sellerPayouts = new Collection;
        $totalAmount = Money::USD(0);

        foreach ($cart as $item) {
            /** @var Product|null $product */
            $product = $products->firstWhere('id', $item->productId);

            if (! $product) {
                throw new ModelNotFoundException(sprintf('Product with ID %s from cart not found in the provided product collection.', $item->productId));
            }

            $itemTotal = $product->price->multiply($item->quantity);
            $totalAmount = $totalAmount->add($itemTotal);

            $sellerId = $product->seller->id;
            $currentPayout = $sellerPayouts->get($sellerId, Money::USD(0));
            $sellerPayouts->put($sellerId, $currentPayout->add($itemTotal));
        }

        return new CalculationResult($totalAmount, $sellerPayouts, $products);
    }
}
