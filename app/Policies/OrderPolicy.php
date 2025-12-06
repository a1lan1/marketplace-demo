<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->isBuyer()) {
            return true;
        }

        return $user->isAdminOrManager();
    }

    public function view(User $user, Order $order): bool
    {
        if ($this->isProductOwner($user, $order)) {
            return true;
        }

        return $user->isAdminOrManager();
    }

    public function create(User $user): bool
    {
        if ($user->isBuyer()) {
            return true;
        }

        return $user->isAdminOrManager();
    }

    public function update(User $user): bool
    {
        return $user->isAdminOrManager();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewChat(User $user, Order $order): bool
    {
        if ($user->isAdminOrManager() || $this->isProductOwner($user, $order)) {
            return true;
        }

        // Or if they are a seller of any product in this order
        return $order->products()->where('user_id', $user->id)->exists();
    }

    public function sendMessage(User $user, Order $order): bool
    {
        return $this->viewChat($user, $order);
    }

    public function isProductOwner(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
