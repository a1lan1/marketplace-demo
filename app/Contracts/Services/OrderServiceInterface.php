<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

interface OrderServiceInterface
{
    public function getUserOrders(User $user, int $perPage = 10): LengthAwarePaginator;

    public function getOrdersForUser(User $user, int $perPage = 10): LengthAwarePaginator;

    /**
     * @param  Collection<int, Product>  $products
     */
    public function createOrder(User $buyer, Money $totalAmount, DataCollection $cart, Collection $products): Order;

    public function findOrderById(int $orderId): Order;
}
