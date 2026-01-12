<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotEnoughStockException extends Exception
{
    public function __construct(Product $product, int $requestedQuantity)
    {
        $message = sprintf('Not enough stock for product: %s. Requested: %d, available: %d.', $product->name, $requestedQuantity, $product->stock);
        parent::__construct($message);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error_code' => 'not_enough_stock',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
