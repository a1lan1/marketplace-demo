<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface UserRepositoryInterface
{
    public function getSellerWithProducts(User $seller, int $productsLimit = 8): User;

    /**
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): User;
}
