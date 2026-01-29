<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Services\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(protected ProductServiceInterface $productService) {}

    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $searchQuery = $request->input('query');

        $products = $this->productService->getAutocompleteSuggestions($searchQuery);

        return ProductResource::collection($products);
    }

    public function getRecommendedProducts(Request $request): AnonymousResourceCollection
    {
        $recommendations = $this->productService->getRecommendedProducts($request->user()->id);

        return ProductResource::collection($recommendations);
    }
}
