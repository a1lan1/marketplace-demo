<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Contracts\ProductServiceInterface;
use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

readonly class CachedProductService implements ProductServiceInterface
{
    public function __construct(private ProductServiceInterface $service) {}

    public function getPaginatedProducts(?string $searchQuery = null, int $perPage = 12, int $page = 1): LengthAwarePaginator
    {
        $key = 'products_catalog_search_'.md5((string) $searchQuery).sprintf('_page_%s_per_%d', $page, $perPage);

        return Cache::tags(['products'])
            ->remember($key, 600, fn (): LengthAwarePaginator => $this->service->getPaginatedProducts($searchQuery, $perPage, $page));
    }

    public function getAutocompleteSuggestions(string $searchQuery, int $limit = 5): Collection
    {
        if (blank($searchQuery)) {
            return collect();
        }

        $key = 'products_autocomplete_'.md5($searchQuery).('_limit_'.$limit);

        return Cache::tags(['products'])
            ->remember($key, 300, fn (): Collection => $this->service->getAutocompleteSuggestions($searchQuery, $limit));
    }

    public function getUserProducts(User $user, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $key = sprintf('products_user_%d_page_%s_per_%d', $user->id, $page, $perPage);

        return Cache::tags(['products'])
            ->remember($key, 3600, fn (): LengthAwarePaginator => $this->service->getUserProducts($user, $perPage, $page));
    }

    public function storeProduct(ProductDTO $productDTO): Product
    {
        return $this->service->storeProduct($productDTO);
    }

    public function deleteProduct(Product $product): void
    {
        $this->service->deleteProduct($product);
    }

    public function getRecommendedProducts(int $userId, ?int $excludedProductId = null): Collection
    {
        $key = sprintf('products_recommendations_user_%d_excluded_', $userId).($excludedProductId ?? 'none');

        return Cache::tags(['products', 'recommendations'])
            ->remember($key, 600, fn (): Collection => $this->service->getRecommendedProducts($userId, $excludedProductId));
    }
}
