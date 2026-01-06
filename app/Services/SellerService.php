<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SellerServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class SellerService implements SellerServiceInterface
{
    public function getSellerWithProducts(User $seller): User
    {
        $key = sprintf('seller_profile_%d_with_products', $seller->id);

        return Cache::tags(['products', 'sellers'])->remember($key, 3600, function () use ($seller): User {
            return $seller->load(['products' => function (HasMany $query): void {
                $query->select(['id', 'user_id', 'name', 'description', 'price', 'stock', 'created_at', 'updated_at'])
                    ->latest()
                    ->take(8);
            }]);
        });
    }
}
