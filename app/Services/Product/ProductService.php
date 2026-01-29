<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Services\NlpSearchPreprocessingServiceInterface;
use App\Contracts\Services\ProductServiceInterface;
use App\Contracts\Services\RecommendationServiceInterface;
use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly RecommendationServiceInterface $recommendationService,
        private readonly NlpSearchPreprocessingServiceInterface $nlpSearchPreprocessingService,
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function getPaginatedProducts(?string $searchQuery = null, int $perPage = 12, int $page = 1): LengthAwarePaginator
    {
        if ($searchQuery) {
            return $this->productRepository->searchPaginated($searchQuery, $perPage, $page);
        }

        return $this->productRepository->getPaginated($perPage, $page);
    }

    public function getAutocompleteSuggestions(string $searchQuery, int $limit = 5): Collection
    {
        if (blank($searchQuery)) {
            return collect();
        }

        $processedQuery = $this->nlpSearchPreprocessingService->preprocessQuery($searchQuery);

        return $this->productRepository->searchSuggestions($processedQuery, $limit);
    }

    public function getUserProducts(User $user, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return $this->productRepository->getForUser($user, $perPage, $page);
    }

    public function storeProduct(ProductDTO $productDTO): Product
    {
        return $this->productRepository->store($productDTO);
    }

    public function deleteProduct(Product $product): void
    {
        $this->productRepository->delete($product);
    }

    public function getRecommendedProducts(int $userId, ?int $excludedProductId = null, ?int $limit = 6): Collection
    {
        $recommendedIds = $this->recommendationService->getRecommendations($userId);

        if ($excludedProductId !== null) {
            $recommendedIds = array_diff($recommendedIds, [$excludedProductId]);
        }

        return $this->productRepository->getRecommended($recommendedIds, $limit);
    }
}
