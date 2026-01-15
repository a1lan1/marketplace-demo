<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Contracts\ProductServiceInterface;
use App\DTO\ProductDTO;
use App\Enums\CacheKeyEnum;
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
        return Cache::tags(['products'])
            ->remember(
                sprintf(CacheKeyEnum::PRODUCTS_CATALOG->value, md5((string) $searchQuery), $page, $perPage),
                600,
                fn (): LengthAwarePaginator => $this->service->getPaginatedProducts($searchQuery, $perPage, $page)
            );
    }

    public function getAutocompleteSuggestions(string $searchQuery, int $limit = 5): Collection
    {
        if (blank($searchQuery)) {
            return collect();
        }

        return Cache::tags(['products'])
            ->remember(
                sprintf(CacheKeyEnum::PRODUCTS_AUTOCOMPLETE->value, md5($searchQuery), $limit),
                300,
                fn (): Collection => $this->service->getAutocompleteSuggestions($searchQuery, $limit)
            );
    }

    public function getUserProducts(User $user, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Cache::tags(['products'])
            ->remember(
                sprintf(CacheKeyEnum::PRODUCTS_USER->value, $user->id, $page, $perPage),
                3600,
                fn (): LengthAwarePaginator => $this->service->getUserProducts($user, $perPage, $page)
            );
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
        return Cache::tags(['products', 'recommendations'])
            ->remember(
                sprintf(CacheKeyEnum::PRODUCTS_RECOMMENDATIONS->value, $userId, $excludedProductId ?? 'none'),
                600,
                fn (): Collection => $this->service->getRecommendedProducts($userId, $excludedProductId)
            );
    }
}
