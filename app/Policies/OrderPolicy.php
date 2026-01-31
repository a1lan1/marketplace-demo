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
        if ($user->isAdminOrManager()) {
            return true;
        }

        if ($this->isOrderBuyer($user, $order)) {
            return true;
        }

        return $order->products()->where('user_id', $user->id)->exists();
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
        return $this->view($user, $order);
    }

    public function sendMessage(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    public function isOrderBuyer(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
