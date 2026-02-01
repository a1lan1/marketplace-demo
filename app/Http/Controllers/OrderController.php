<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PurchaseAction;
use App\Actions\UpdateOrderStatusAction;
use App\Contracts\Services\OrderServiceInterface;
use App\DTO\PurchaseDTO;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Payment\PaymentTypeEnum;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotEnoughStockException;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OrderController extends Controller
{
    public function __construct(protected OrderServiceInterface $orderService) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderService->getUserOrders($request->user());

        return Inertia::render('Order/Index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $detailedOrder = $this->orderService->findOrderById($order->id);

        return Inertia::render('Order/Show', [
            'order' => OrderResource::make($detailedOrder),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(PurchaseRequest $request, PurchaseAction $purchaseAction): RedirectResponse
    {
        $this->authorize('create', Order::class);

        try {
            $purchaseAction->execute(
                PurchaseDTO::from([
                    'buyer' => $request->user(),
                    'cart' => $request->validated('cart'),
                    'paymentType' => PaymentTypeEnum::from($request->validated('payment_type')),
                    'paymentMethodId' => $request->validated('payment_method_id'),
                    'paymentProvider' => $request->validated('payment_provider'),
                    'saveCard' => $request->boolean('save_card'),
                ])
            );
        } catch (InsufficientFundsException) {
            return back()->withErrors(['purchase' => 'You do not have enough funds to complete this purchase.']);
        } catch (NotEnoughStockException $e) {
            return back()->withErrors(['purchase' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['purchase' => 'An unexpected error occurred. Please try again later.']);
        }

        return to_route('orders.index')->with('success', 'Order placed successfully.');
    }

    /**
     * @throws AuthorizationException
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusAction $updateOrderStatus): RedirectResponse
    {
        $this->authorize('update', Order::class);

        $updateOrderStatus->execute(
            order: $order,
            status: OrderStatusEnum::from($request->validated('status'))
        );

        return back()->with('success', 'Order status updated.');
    }
}
