<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\ProductServiceInterface;
use App\DTO\ProductDTO;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ProductServiceInterface $productService) {}

    public function catalog(ProductFilterRequest $request): Response
    {
        $products = $this->productService->getPaginatedProducts(
            $request->validated('search')
        );

        return Inertia::render('Products/Catalog', [
            'products' => ProductResource::collection($products)->response()->getData(true),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Product $product, Request $request): Response
    {
        $this->authorize('view', $product);

        $product->load('seller');

        return Inertia::render('Products/Show', [
            'product' => ProductResource::make($product),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->productService->getUserProducts($request->user());

        return Inertia::render('Products/Index', [
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Products/Create');
    }

    /**
     * @throws AuthorizationException
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $productDTO = new ProductDTO(
            user: $request->user(),
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: (int) $request->validated('price'),
            stock: (int) $request->validated('stock'),
            coverImage: $request->file('cover_image'),
        );

        $this->productService->storeProduct($productDTO);

        return to_route('products.index')->with('success', 'Product created.');
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('Products/Edit', [
            'product' => ProductResource::make($product),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $productDTO = new ProductDTO(
            user: $request->user(),
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: (int) $request->validated('price'),
            stock: (int) $request->validated('stock'),
            coverImage: $request->file('cover_image'),
            productId: $product->id,
        );

        $this->productService->storeProduct($productDTO);

        return to_route('products.index')->with('success', 'Product updated.');
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return to_route('products.index')->with('success', 'Product deleted.');
    }

    public function getRecommendedProducts(Request $request): JsonResponse
    {
        $recommendations = $this->productService->getRecommendedProducts($request->user()->id);

        return ProductResource::collection($recommendations)->response();
    }
}
