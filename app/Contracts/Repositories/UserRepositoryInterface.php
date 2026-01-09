<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getSellerWithProducts(User $seller, int $productsLimit = 8): User;
}
