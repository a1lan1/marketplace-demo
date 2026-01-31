<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\SalesStatsDTO;
use App\Enums\Order\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class OrderRepository implements OrderRepositoryInterface
{
    public function sumTotalAmountByStatus(OrderStatusEnum $status): int
    {
        return (int) Order::query()
            ->where('status', $status)
            ->sum('total_amount');
    }

    public function getSalesStatsByCurrency(): ?SalesStatsDTO
    {
        $stats = Order::query()
            ->selectRaw('count(*) as count, sum(total_amount) as total')
            ->toBase()
            ->first();

        if (! $stats || ! $stats->count) {
            return null;
        }

        return new SalesStatsDTO(
            count: (int) $stats->count,
            totalCents: (int) $stats->total
        );
    }

    public function hasPurchasedProduct(int $userId, int $productId): bool
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('status', OrderStatusEnum::COMPLETED)
            ->whereHas('products', function (Builder $query) use ($productId): void {
                $query->where('products.id', $productId);
            })
            ->exists();
    }

    public function create(User $buyer, Money $totalAmount): Order
    {
        /** @var Order $order */
        $order = Order::create([
            'user_id' => $buyer->id,
            'total_amount' => $totalAmount,
        ]);

        return $order;
    }

    public function attachProducts(Order $order, array $attachments): void
    {
        $order->products()->attach($attachments);
    }

    public function updateStatus(Order $order, OrderStatusEnum $status): void
    {
        $order->update(['status' => $status]);
    }

    public function paginateByBuyer(User $buyer, int $perPage = 20): LengthAwarePaginator
    {
        return Order::query()
            ->withEssentialRelations()
            ->where('user_id', $buyer->id)
            ->latest()
            ->paginate($perPage);
    }

    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
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

    public function findByIdWithDetails(int $orderId): Order
    {
        return Order::query()
            ->with([
                'buyer:id,name,email',
                'products:id,name,user_id',
                'products.media',
                'products.seller:id,name',
                'payment:id,order_id,provider',
                'transaction:id,order_id',
            ])
            ->findOrFail($orderId);
    }
}
