<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;

class OrderService implements OrderServiceInterface
{
    public function getUserOrders(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Order::query()
            ->withEssentialRelations()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get orders for a user based on their role.
     * Admins/Managers see all orders, others see their own.
     */
    public function getOrdersForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Order::query()
            ->withEssentialRelations()
            ->with(['products.seller' => function (Relation $query): void {
                $query->select('id', 'name')->with('media');
            }])
            ->forUser($user)
            ->latest()
            ->paginate($perPage);
    }
}
