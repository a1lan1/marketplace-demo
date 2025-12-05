<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PurchaseAction;
use App\Contracts\OrderServiceInterface;
use App\DTO\PurchaseDTO;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotEnoughStockException;
use App\Http\Requests\PurchaseRequest;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Throwable;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected OrderServiceInterface $orderRetrievalService) {}

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
}
