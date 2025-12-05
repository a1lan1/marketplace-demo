<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\ProductServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductServiceInterface $productService,
    ) {}

    public function autocomplete(Request $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        $products = $this->productService->getAutocompleteSuggestions($searchQuery);

        return response()->json($products);
    }
}
