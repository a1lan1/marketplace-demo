<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductServiceInterface $productService) {}

    public function autocomplete(Request $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        $products = $this->productService->getAutocompleteSuggestions($searchQuery);

        return response()->json($products);
    }

    public function getRecommendedProducts(Request $request): JsonResponse
    {
        $recommendations = $this->productService->getRecommendedProducts($request->user()->id);

        return ProductResource::collection($recommendations)->response();
    }
}
