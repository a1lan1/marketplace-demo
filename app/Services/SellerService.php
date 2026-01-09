<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\SellerServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SellerService implements SellerServiceInterface
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    public function getSellerWithProducts(User $seller): User
    {
        $key = sprintf('seller_profile_%d_with_products', $seller->id);

        return Cache::tags(['products', 'sellers'])->remember($key, 3600, function () use ($seller): User {
            return $this->userRepository->getSellerWithProducts($seller);
        });
    }
}
