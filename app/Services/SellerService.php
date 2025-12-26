<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SellerServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerService implements SellerServiceInterface
{
    public function getSellerWithProducts(User $seller): User
    {
        return $seller->load(['products' => function (HasMany $query): void {
            $query->latest()->take(8);
        }]);
    }
}
