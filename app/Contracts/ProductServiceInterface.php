<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductServiceInterface
{
    public function getPaginatedProducts(?string $searchQuery = null, int $perPage = 12): LengthAwarePaginator;

    public function getAutocompleteSuggestions(string $searchQuery, int $limit = 5): Collection;

    public function getUserProducts(User $user, int $perPage = 10): LengthAwarePaginator;

    public function storeProduct(ProductDTO $productDTO): Product;

    public function deleteProduct(Product $product): void;

    public function getRecommendedProducts(int $userId, ?int $excludedProductId = null): Collection;
}
