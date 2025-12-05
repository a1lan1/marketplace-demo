<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PurchaseAction;
use App\Actions\UpdateOrderStatusAction;
use App\Contracts\OrderServiceInterface;
use App\DTO\PurchaseDTO;
use App\Enums\OrderStatusEnum;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotEnoughStockException;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected OrderServiceInterface $orderRetrievalService) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderRetrievalService->getUserOrders($request->user());

        return Inertia::render('Orders', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(PurchaseRequest $request, PurchaseAction $purchaseAction): RedirectResponse
    {
        $this->authorize('create', Order::class);

        try {
            $purchaseDTO = PurchaseDTO::from([
                'buyer' => $request->user(),
                'cart' => $request->validated('cart'),
            ]);

            $purchaseAction->execute($purchaseDTO);
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

        $status = OrderStatusEnum::from($request->validated('status'));

        $updateOrderStatus->execute($order, $status);

        return back()->with('success', 'Order status updated.');
    }
}
