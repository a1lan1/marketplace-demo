<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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

    /**
     * @throws ModelNotFoundException
     */
    public function findByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /**
     * @return Collection<int, User>
     */
    public function searchByNameOrEmail(string $query, int $limit = 20, ?int $excludeUserId = null): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->with('media')
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->when($query, function ($q) use ($query): void {
                $q->where(function ($sub) use ($query): void {
                    $sub->where('email', 'like', sprintf('%%%s%%', $query))
                        ->orWhere('name', 'like', sprintf('%%%s%%', $query));
                });
            })
            ->limit($limit)
            ->get();
    }
}
