<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->id === $product->user_id || $user->isAdminOrManager();
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->id === $product->user_id && $user->can('products.edit-own')) {
            return true;
        }

        return $user->isAdminOrManager();
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->id === $product->user_id && $user->can('products.delete-own')) {
            return true;
        }

        return $user->isAdminOrManager();
    }
}
