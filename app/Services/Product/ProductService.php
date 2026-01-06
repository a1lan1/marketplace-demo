<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Contracts\ProductSearcherInterface;
use App\Contracts\ProductServiceInterface;
use App\Contracts\RecommendationServiceInterface;
use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly RecommendationServiceInterface $recommendationService,
        private readonly NlpSearchPreprocessingServiceInterface $nlpSearchPreprocessingService,
        private readonly ProductSearcherInterface $productSearcher,
    ) {}

    public function getPaginatedProducts(?string $searchQuery = null, int $perPage = 12, int $page = 1): LengthAwarePaginator
    {
        if ($searchQuery) {
            return $this->productSearcher
                ->search($searchQuery)
                ->paginate($perPage, 'page', $page);
        }

        return Product::query()
            ->select(['id', 'user_id', 'name', 'description', 'price', 'stock', 'created_at', 'updated_at'])
            ->with([
                'seller' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getAutocompleteSuggestions(string $searchQuery, int $limit = 5): Collection
    {
        if (blank($searchQuery)) {
            return collect();
        }

        $processedQuery = $this->nlpSearchPreprocessingService->preprocessQuery($searchQuery);

        return $this->productSearcher
            ->search($processedQuery)
            ->take($limit)
            ->get()
            ->map(fn (Model $product) => $product->only('id', 'name'));
    }

    public function getUserProducts(User $user, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return $user->products()
            ->select(['id', 'user_id', 'name', 'description', 'price', 'stock', 'created_at', 'updated_at'])
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function storeProduct(ProductDTO $productDTO): Product
    {
        /** @var Product $product */
        $product = $productDTO->user->products()->updateOrCreate(
            ['id' => $productDTO->productId],
            $productDTO->toArray()
        );

        if ($productDTO->coverImage instanceof UploadedFile) {
            $product->uploadCoverImage($productDTO->coverImage);
        }

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    public function getRecommendedProducts(int $userId, ?int $excludedProductId = null, ?int $limit = 6): Collection
    {
        $recommendedIds = $this->recommendationService->getRecommendations($userId);

        if ($excludedProductId !== null) {
            $recommendedIds = array_diff($recommendedIds, [$excludedProductId]);
        }

        if ($recommendedIds === []) {
            return new Collection;
        }

        return Product::query()
            ->whereIn('id', $recommendedIds)
            ->select(['id', 'user_id', 'name', 'price', 'stock'])
            ->orderByRaw('array_position(ARRAY['.implode(',', $recommendedIds).']::bigint[], id::bigint)')
            ->with([
                'media',
                'seller' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->limit($limit)
            ->get();
    }
}
