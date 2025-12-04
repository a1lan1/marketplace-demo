<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use Exception;

class NotEnoughStockException extends Exception
{
    public function __construct(Product $product, int $requestedQuantity)
    {
        $message = sprintf('Not enough stock for product: %s. Requested: %d, available: %d.', $product->name, $requestedQuantity, $product->stock);
        parent::__construct($message);
    }
}
