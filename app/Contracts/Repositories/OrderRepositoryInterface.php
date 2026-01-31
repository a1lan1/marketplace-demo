<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\SalesStatsDTO;
use App\Enums\Order\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function sumTotalAmountByStatus(OrderStatusEnum $status): int;

    public function getSalesStatsByCurrency(): ?SalesStatsDTO;

    public function hasPurchasedProduct(int $userId, int $productId): bool;

    public function create(User $buyer, Money $totalAmount): Order;

    public function attachProducts(Order $order, array $attachments): void;

    public function updateStatus(Order $order, OrderStatusEnum $status): void;

    public function paginateByBuyer(User $buyer, int $perPage = 20): LengthAwarePaginator;

    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator;

    public function findByIdWithDetails(int $orderId): Order;
}
