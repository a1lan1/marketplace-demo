<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\BalanceServiceInterface;
use App\DTO\PurchaseDTO;
use App\Events\OrderCreated;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotEnoughStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\DataCollection;
use Throwable;

readonly class PurchaseAction
{
    public function __construct(private BalanceServiceInterface $balanceService) {}

    /**
     * @throws InsufficientFundsException
     * @throws NotEnoughStockException
     * @throws Throwable
     */
    public function execute(PurchaseDTO $purchaseDTO): void
    {
        $data = $this->prepareAndValidateData($purchaseDTO);

        $order = DB::transaction(function () use ($purchaseDTO, $data): Order {
            $order = $this->createOrderAndWithdraw($purchaseDTO->buyer, $data['totalAmount']);
            $this->processOrderItems($order, $purchaseDTO->cart, $data['products']);
            $this->distributePayouts($order, $data['sellerPayouts']);

            return $order;
        });

        event(new OrderCreated($order));
    }

    /**
     * @throws NotEnoughStockException
     */
    private function prepareAndValidateData(PurchaseDTO $purchaseDTO): array
    {
        $sellerPayouts = new Collection;
        $totalAmount = Money::USD(0);

        $productIds = $purchaseDTO->cart->toCollection()->pluck('productId')->unique()->all();
        $products = Product::with('seller')->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($purchaseDTO->cart as $item) {
            /** @var Product $product */
            $product = $products->get($item->productId);

            if ($product->stock < $item->quantity) {
                throw new NotEnoughStockException($product, $item->quantity);
            }

            $itemTotal = $product->price->multiply($item->quantity);
            $totalAmount = $totalAmount->add($itemTotal);

            $sellerId = $product->seller->id;
            $currentPayout = $sellerPayouts->get($sellerId, Money::USD(0));
            $sellerPayouts->put($sellerId, $currentPayout->add($itemTotal));
        }

        return [
            'products' => $products,
            'totalAmount' => $totalAmount,
            'sellerPayouts' => $sellerPayouts,
        ];
    }

    /**
     * @throws InsufficientFundsException
     * @throws Throwable
     */
    private function createOrderAndWithdraw(User $buyer, Money $totalAmount): Order
    {
        /** @var Order $order */
        $order = $buyer->orders()->create(['total_amount' => $totalAmount]);

        $this->balanceService->withdraw(
            user: $buyer,
            amount: $totalAmount,
            description: 'Payment for order #'.$order->id
        );

        return $order;
    }

    private function processOrderItems(Order $order, DataCollection $cart, Collection $products): void
    {
        foreach ($cart as $item) {
            /** @var Product $product */
            $product = $products->get($item->productId);

            $order->products()->attach($product->id, [
                'quantity' => $item->quantity,
                'price' => $product->price,
            ]);

            $product->decrement('stock', $item->quantity);
        }
    }

    /**
     * @throws Throwable
     */
    private function distributePayouts(Order $order, Collection $sellerPayouts): void
    {
        $sellerIds = $sellerPayouts->keys()->all();
        $sellers = User::whereIn('id', $sellerIds)->get()->keyBy('id');

        foreach ($sellerPayouts as $sellerId => $payoutAmount) {
            /** @var User $seller */
            $seller = $sellers->get($sellerId);

            $this->balanceService->deposit(
                user: $seller,
                amount: $payoutAmount,
                description: 'Payout for order #'.$order->id
            );
        }
    }
}
