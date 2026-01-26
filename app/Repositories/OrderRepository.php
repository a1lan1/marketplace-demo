<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\SalesStatsDTO;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Builder;

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
}
