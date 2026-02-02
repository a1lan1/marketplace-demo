<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\PayoutMethodRepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Http\Resources\PayoutMethodResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserBalanceResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function __construct(
        protected TransactionRepositoryInterface $transactionRepository,
        protected PayoutMethodRepositoryInterface $payoutMethodRepository,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $transactions = $this->transactionRepository->paginateForUser($user);
        $payoutMethods = $this->payoutMethodRepository->getForUser($user);

        return Inertia::render('Wallet', [
            'balance' => UserBalanceResource::make($user),
            'transactions' => TransactionResource::collection($transactions),
            'payoutMethods' => PayoutMethodResource::collection($payoutMethods),
        ]);
    }
}
