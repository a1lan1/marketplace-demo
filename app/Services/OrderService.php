<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class OrderService implements OrderServiceInterface
{
    public function getUserOrders(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->orders()
            ->select(['id', 'user_id', 'total_amount', 'status', 'created_at'])
            ->with([
                'products' => function (Relation $query): void {
                    $query->select(['products.id', 'products.name', 'products.price'])->with('media');
                },
                'buyer' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
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
            ->select(['id', 'user_id', 'total_amount', 'status', 'created_at'])
            ->with([
                'buyer' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
                'products.seller' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->latest()
            ->get();
    }
}
