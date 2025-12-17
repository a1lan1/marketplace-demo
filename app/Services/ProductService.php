<?php

declare(strict_types=1);

namespace App\Services;

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

    public function getPaginatedProducts(?string $searchQuery = null, int $perPage = 12): LengthAwarePaginator
    {
        if ($searchQuery) {
            return Product::search($searchQuery)->paginate($perPage);
        }

        return Product::query()
            ->latest()
            ->paginate($perPage);
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

    public function getUserProducts(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->products()->latest()->paginate($perPage);
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

    public function getRecommendedProducts(int $userId, ?int $excludedProductId = null): Collection
    {
        return $this->recommendationService->getRecommendedProducts($userId, $excludedProductId);
    }
}
