<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function getPaginated(int $perPage = 12, int $page = 1): LengthAwarePaginator;

    public function getForUser(User $user, int $perPage = 10, int $page = 1): LengthAwarePaginator;

    public function store(ProductDTO $productDTO): Product;

    public function delete(Product $product): void;

    public function getRecommended(array $ids, int $limit = 6): Collection;

    public function getByIdsLocked(array $ids): Collection;

    public function searchPaginated(string $query, int $perPage = 12, int $page = 1): LengthAwarePaginator;

    public function searchSuggestions(string $query, int $limit = 5): Collection;
}
