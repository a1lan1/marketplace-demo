<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\ProductServiceInterface;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ProductServiceInterface $productService) {}

    public function catalog(Request $request): Response
    {
        $request->validate(['search' => 'sometimes|string|nullable|min:3|max:255']);

        $products = $this->productService->getPaginatedProducts(
            $request->input('search')
        );

        return Inertia::render('Products/Catalog', [
            'products' => $products,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Product $product): Response
    {
        $this->authorize('view', $product);

        $product->load('seller');

        return Inertia::render('Products/Show', [
            'product' => ProductResource::make($product),
        ]);
    }
}
