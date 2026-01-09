<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\SalesStatsDTO;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;

interface OrderRepositoryInterface
{
    public function sumTotalAmountByStatus(OrderStatusEnum $status): int;

    public function getSalesStatsByCurrency(): ?SalesStatsDTO;

    public function hasPurchasedProduct(int $userId, int $productId): bool;

    public function create(User $buyer, Money $totalAmount): Order;

    public function attachProducts(Order $order, array $attachments): void;

    public function updateStatus(Order $order, OrderStatusEnum $status): void;
}
