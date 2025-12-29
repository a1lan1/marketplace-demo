<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderService implements OrderServiceInterface
{
    public function getUserOrders(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->orders()
            ->with('products', 'buyer')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get orders for a user based on their role.
     * Admins/Managers see all orders, others see their own.
     */
    public function getOrdersForUser(User $user): Collection
    {
        return Order::query()
            ->forUser($user)
            ->with(['buyer', 'products.seller'])
            ->latest()
            ->get();
    }
}
