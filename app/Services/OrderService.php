<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    public function getUserOrders(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->orderRepository->paginateByBuyer($user, $perPage);
    }

    /**
     * Get orders for a user based on their role.
     * Admins/Managers see all orders, others see their own.
     */
    public function getOrdersForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->orderRepository->paginateForUser($user, $perPage);
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    public function createOrder(User $buyer, Money $totalAmount, DataCollection $cart, Collection $products): Order
    {
        $order = $this->orderRepository->create($buyer, $totalAmount);

        $attachments = [];
        foreach ($cart as $item) {
            $product = $products->firstWhere('id', $item->productId);

            if ($product) {
                $attachments[$item->productId] = [
                    'quantity' => $item->quantity,
                    'price' => $product->price->getAmount(),
                ];
            }
        }

        $this->orderRepository->attachProducts($order, $attachments);

        return $order;
    }

    public function findOrderById(int $orderId): Order
    {
        return $this->orderRepository->findByIdWithDetails($orderId);
    }
}
