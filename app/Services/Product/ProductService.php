<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\Contracts\RecommendationServiceInterface;
use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

readonly class ProductService implements ProductServiceInterface
{
    public function __construct(
        private RecommendationServiceInterface $recommendationService,
        private NlpSearchPreprocessingServiceInterface $nlpSearchPreprocessingService,
    ) {}

    public function getPaginatedProducts(?string $searchQuery = null, int $perPage = 12, int $page = 1): LengthAwarePaginator
    {
        if ($searchQuery) {
            return Product::search($searchQuery)->paginate($perPage, 'page', $page);
        }

        return Product::query()
            ->with('seller')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getAutocompleteSuggestions(string $searchQuery, int $limit = 5): Collection
    {
        if (blank($searchQuery)) {
            return collect();
        }

        $processedQuery = $this->nlpSearchPreprocessingService->preprocessQuery($searchQuery);

        return Product::search($processedQuery)
            ->take($limit)
            ->get();
    }

    public function getUserProducts(User $user, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return $user->products()->latest()->paginate($perPage, ['*'], 'page', $page);
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
            ->orderByRaw('array_position(ARRAY['.implode(',', $recommendedIds).']::bigint[], id::bigint)')
            ->with(['media', 'seller'])
            ->limit($limit)
            ->get();
    }
}
