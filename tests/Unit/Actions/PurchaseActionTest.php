<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\PurchaseAction;
use App\Actions\Transactions\CreateTransactionAction;
use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\CartItemDTO;
use App\DTO\PurchaseDTO;
use App\Enums\Payment\PaymentTypeEnum;
use App\Enums\Transaction\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotEnoughStockException;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use App\Services\BalanceService;
use App\Services\OrderService;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\PaymentProcessors\PaymentProcessorFactory;
use App\Services\Purchase\CartCalculator;
use App\Services\Purchase\InventoryService;
use App\Services\Purchase\PayoutDistributor;
use Illuminate\Support\Facades\Notification;
use Spatie\LaravelData\DataCollection;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Notification::fake();

    $this->cartCalculator = new CartCalculator;
    $this->inventoryService = new InventoryService;
    $this->transactionRepository = new TransactionRepository;
    $this->orderRepository = new OrderRepository;
    $this->productRepository = new ProductRepository;
    $this->orderService = new OrderService($this->orderRepository);
    $this->paymentGatewayFactory = new PaymentGatewayFactory($this->app);

    $this->balanceService = new BalanceService(
        new CreateTransactionAction($this->transactionRepository),
        $this->paymentGatewayFactory
    );

    $this->payoutDistributor = new PayoutDistributor($this->balanceService);

    $this->app->instance(BalanceServiceInterface::class, $this->balanceService);

    $this->paymentProcessorFactory = new PaymentProcessorFactory($this->app);

    $this->purchaseAction = new PurchaseAction(
        $this->cartCalculator,
        $this->inventoryService,
        $this->orderService,
        $this->productRepository,
        $this->paymentProcessorFactory
    );
});

it('executes purchase successfully', function (): void {
    // Arrange
    $buyer = User::factory()->create(['balance' => 100000]); // $1000.00
    $seller = User::factory()->create(['balance' => 0]);

    $product1 = Product::factory()->create([
        'user_id' => $seller->id,
        'price' => 10000, // $100.00
        'stock' => 10,
    ]);

    $product2 = Product::factory()->create([
        'user_id' => $seller->id,
        'price' => 5000, // $50.00
        'stock' => 5,
    ]);

    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO($product1->id, 2), // $200.00
        new CartItemDTO($product2->id, 1), // $50.00
    ]);

    $dto = new PurchaseDTO($buyer, $cart, PaymentTypeEnum::BALANCE);

    // Act
    $this->purchaseAction->execute($dto);

    // Assert
    // Check Buyer Balance (1000 - 250 = 750)
    expect((int) $buyer->fresh()->balance->getAmount())->toBe(75000);

    // Check Seller Balance (0 + 250 = 250)
    expect((int) $seller->fresh()->balance->getAmount())->toBe(25000);

    // Check Stock
    expect($product1->fresh()->stock)->toBe(8);
    expect($product2->fresh()->stock)->toBe(4);

    // Check Order Created
    assertDatabaseHas('orders', [
        'user_id' => $buyer->id,
        'total_amount' => 25000,
    ]);

    // Check Transactions
    assertDatabaseHas('transactions', [
        'user_id' => $buyer->id,
        'amount' => 25000,
        'type' => TransactionType::PURCHASE->value,
    ]);

    assertDatabaseHas('transactions', [
        'user_id' => $seller->id,
        'amount' => 25000,
        'type' => TransactionType::DEPOSIT->value,
    ]);
});

it('throws exception if not enough stock', function (): void {
    // Arrange
    $buyer = User::factory()->create(['balance' => 100000]);
    $product = Product::factory()->create(['stock' => 1]);

    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO($product->id, 2),
    ]);

    $dto = new PurchaseDTO($buyer, $cart, PaymentTypeEnum::BALANCE);

    // Act & Assert
    $this->purchaseAction->execute($dto);
})->throws(NotEnoughStockException::class);

it('throws exception if insufficient funds', function (): void {
    // Arrange
    $buyer = User::factory()->create(['balance' => 1000]);
    $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);

    $cart = new DataCollection(CartItemDTO::class, [
        new CartItemDTO($product->id, 1),
    ]);

    $dto = new PurchaseDTO($buyer, $cart, PaymentTypeEnum::BALANCE);

    // Act & Assert
    $this->purchaseAction->execute($dto);
})->throws(InsufficientFundsException::class);
