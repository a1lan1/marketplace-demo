<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function getSellerWithProducts(User $seller, int $productsLimit = 8): User;

    /**
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): User;

    /**
     * @throws ModelNotFoundException
     */
    public function findByEmail(string $email): User;

    /**
     * @return Collection<int, User>
     */
    public function searchByNameOrEmail(string $query, int $limit = 20, ?int $excludeUserId = null): Collection;
}
