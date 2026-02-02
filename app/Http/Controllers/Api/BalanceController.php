<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Payment\DepositAction;
use App\Actions\Payment\TransferAction;
use App\Actions\Payment\WithdrawAction;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\Payment\ProcessPaymentDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\SearchRecipientsRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserBalanceResource;
use App\Http\Resources\UserResource;
use Cknow\Money\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class BalanceController extends Controller
{
    public function __construct(
        protected BalanceServiceInterface $balanceService,
        protected TransactionRepositoryInterface $transactionRepository,
        protected UserRepositoryInterface $userRepository,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'balance' => UserBalanceResource::make($request->user()),
        ]);
    }

    public function transactions(Request $request): AnonymousResourceCollection
    {
        $transactions = $this->transactionRepository->paginateForUser($request->user());

        return TransactionResource::collection($transactions);
    }

    public function recipients(SearchRecipientsRequest $request): AnonymousResourceCollection
    {
        $search = $request->string('search')->toString();

        $users = $this->userRepository->searchByNameOrEmail(
            query: $search,
            excludeUserId: $request->user()->id
        );

        return UserResource::collection($users);
    }

    public function deposit(DepositRequest $request, DepositAction $action): JsonResponse
    {
        $user = $request->user();
        $amount = Money::parse(
            $request->integer('amount'),
            $request->validated('currency')
        );

        try {
            $result = $action->execute(
                ProcessPaymentDTO::make([
                    'user' => $user,
                    'amount' => $amount->getAmount(),
                    'currency' => $amount->getCurrency()->getCode(),
                    'paymentMethodId' => $request->validated('payment_method_id'),
                    'saveCard' => $request->boolean('save_card'),
                    'provider' => $request->enum('provider', PaymentProviderEnum::class),
                    'idempotencyKey' => $request->header('Idempotency-Key'),
                ])
            );

            if ($result->status === PaymentStatusEnum::SUCCEEDED) {
                return response()->json([
                    'message' => 'Funds deposited successfully.',
                    'balance' => $user->fresh()->balance->getAmount(),
                ]);
            }

            return response()->json([
                'message' => 'Payment processing...',
                'payment_id' => $result->paymentId,
            ], 202);
        } catch (PaymentGatewayException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }

    public function withdraw(WithdrawRequest $request, WithdrawAction $action): JsonResponse
    {
        $user = $request->user();

        try {
            $action->execute(
                $user,
                $request->validated('payout_method_id'),
                Money::parse($request->integer('amount'), $request->validated('currency')),
                $request->validated('description')
            );

            return response()->json([
                'message' => 'Funds withdrawn successfully.',
                'balance' => $user->fresh()->balance->getAmount(),
            ]);
        } catch (InsufficientFundsException) {
            return response()->json(['error' => 'Insufficient funds.'], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }

    public function transfer(TransferRequest $request, TransferAction $action): JsonResponse
    {
        $sender = $request->user();
        $recipient = $this->userRepository->findByEmail($request->validated('email'));

        try {
            $action->execute(
                $sender,
                $recipient->id,
                Money::parse($request->integer('amount'), $request->validated('currency')),
                $request->validated('description')
            );

            return response()->json([
                'message' => 'Funds transferred successfully.',
                'balance' => $sender->fresh()->balance->getAmount(),
            ]);
        } catch (InsufficientFundsException) {
            return response()->json(['error' => 'Insufficient funds.'], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }
}
