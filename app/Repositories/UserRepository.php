<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserRepository implements UserRepositoryInterface
{
    public function getSellerWithProducts(User $seller, int $productsLimit = 8): User
    {
        return $seller->load(['products' => function (HasMany $query) use ($productsLimit): void {
            $query->select(['id', 'user_id', 'name', 'description', 'price', 'stock', 'created_at', 'updated_at'])
                ->latest()
                ->take($productsLimit);
        }]);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): User
    {
        return User::query()
            ->select(['id', 'name', 'balance'])
            ->findOrFail($id);
    }
}
