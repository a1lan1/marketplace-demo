<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTO\PurchaseDTO;
use App\Enums\Order\OrderStatusEnum;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderCreationAttempted;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotEnoughStockException;
use App\Models\Order;
use App\Services\PaymentProcessors\PaymentProcessorFactory;
use App\Services\Purchase\CartCalculator;
use App\Services\Purchase\InventoryService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class PurchaseAction
{
    public function __construct(
        private CartCalculator $cartCalculator,
        private InventoryService $inventoryService,
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private PaymentProcessorFactory $paymentProcessorFactory
    ) {}

    /**
     * @throws InsufficientFundsException
     * @throws NotEnoughStockException
     * @throws Throwable
     */
    public function execute(PurchaseDTO $purchaseDTO): void
    {
        event(new OrderCreationAttempted($purchaseDTO));

        // Fetch Products
        $productIds = $purchaseDTO->cart->toCollection()->pluck('productId')->unique()->all();

        DB::transaction(function () use ($purchaseDTO, $productIds): Order {
            // Lock products for update to prevent concurrent purchases of the same stock
            $products = $this->productRepository->getByIdsLocked($productIds);

            // Validate Stock
            $this->inventoryService->ensureStock($purchaseDTO->cart, $products);

            // Calculate Totals
            $calculation = $this->cartCalculator->calculate($purchaseDTO->cart, $products);

            // Create Order
            $order = $this->orderRepository->create($purchaseDTO->buyer, $calculation->totalAmount);

            // Process Payment
            $paymentProcessor = $this->paymentProcessorFactory->make($purchaseDTO->paymentType);
            $paymentProcessor->process($purchaseDTO, $order, $calculation->totalAmount);

            // Update Order Status
            $order->updateStatus(OrderStatusEnum::PAID);

            // Process Items (Attach & Decrement)
            $this->inventoryService->decrementStock($purchaseDTO->cart, $products);

            // Dispatch OrderCreated event with all necessary data
            event(new OrderCreated($order, $calculation->sellerPayouts));

            return $order;
        });
    }
}
