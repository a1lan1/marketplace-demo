<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\OrderServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrderServiceInterface $orderService,
    ) {}

    public function index(): Response
    {
        $orders = $this->orderService->getOrdersForUser(Auth::user());

        return Inertia::render('Chat', [
            'orders' => $orders,
        ]);
    }
}
