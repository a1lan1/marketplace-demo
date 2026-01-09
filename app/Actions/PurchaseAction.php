<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\BalanceServiceInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\PurchaseDTO;
use App\Events\OrderCreated;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotEnoughStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Purchase\CartCalculator;
use App\Services\Purchase\InventoryService;
use App\Services\Purchase\PayoutDistributor;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\DataCollection;
use Throwable;

readonly class PurchaseAction
{
    public function __construct(
        private BalanceServiceInterface $balanceService,
        private CartCalculator $cartCalculator,
        private InventoryService $inventoryService,
        private PayoutDistributor $payoutDistributor,
        private OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * @throws InsufficientFundsException
     * @throws NotEnoughStockException
     * @throws Throwable
     */
    public function execute(PurchaseDTO $purchaseDTO): void
    {
        // Fetch Products
        $productIds = $purchaseDTO->cart->toCollection()->pluck('productId')->unique()->all();

        $order = DB::transaction(function () use ($purchaseDTO, $productIds): Order {
            // Lock products for update to prevent concurrent purchases of the same stock
            $products = Product::with('seller')
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate Stock
            $this->inventoryService->ensureStock($purchaseDTO->cart, $products);

            // Calculate Totals
            $calculation = $this->cartCalculator->calculate($purchaseDTO->cart, $products);

            // Create Order & Charge Buyer
            $order = $this->createOrderAndWithdraw($purchaseDTO->buyer, $calculation->totalAmount);

            // Process Items (Attach & Decrement)
            $this->processOrderItems($order, $purchaseDTO->cart, $products);
            $this->inventoryService->decrementStock($purchaseDTO->cart, $products);

            // Distribute Payouts
            $sellers = $products->pluck('seller')->unique('id')->keyBy('id');
            $this->payoutDistributor->distribute($order, $calculation->sellerPayouts, $sellers);

            return $order;
        });

        event(new OrderCreated($order));
    }

    /**
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    private function createOrderAndWithdraw(User $buyer, Money $totalAmount): Order
    {
        $order = $this->orderRepository->create($buyer, $totalAmount);

        $this->balanceService->withdraw(
            user: $buyer,
            amount: $totalAmount,
            description: 'Payment for order #'.$order->id
        );

        return $order;
    }

    private function processOrderItems(Order $order, DataCollection $cart, Collection $products): void
    {
        $cartItems = $cart->toCollection()->keyBy('productId');

        $attachments = $products->mapWithKeys(fn (Product $product): array => [
            $product->id => [
                'quantity' => $cartItems->get($product->id)->quantity,
                'price' => $product->price->getAmount(),
            ],
        ])->all();

        $this->orderRepository->attachProducts($order, $attachments);
    }
}
