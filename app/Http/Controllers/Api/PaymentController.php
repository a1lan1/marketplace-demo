<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\DTO\Payment\ProcessPaymentDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Requests\SetupIntentRequest;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PaymentMethod::class);

        $methods = $this->paymentRepository->getUserPaymentMethods($request->user()->id);

        return PaymentMethodResource::collection($methods);
    }

    /**
     * @throws BindingResolutionException|AuthorizationException
     */
    public function setupIntent(SetupIntentRequest $request): JsonResponse
    {
        $this->authorize('create', PaymentMethod::class);

        try {
            $setupIntent = $this->paymentService->createSetupIntent(
                $request->user(),
                $request->enum('provider', PaymentProviderEnum::class)
            );

            return response()->json($setupIntent->toArray(), Response::HTTP_CREATED);
        } catch (PaymentGatewayException $paymentGatewayException) {
            report($paymentGatewayException);

            return response()->json(['error' => 'Failed to create setup intent.'], 500);
        }
    }

    /**
     * @throws BindingResolutionException|AuthorizationException
     */
    public function storeMethod(StorePaymentMethodRequest $request): PaymentMethodResource
    {
        $this->authorize('create', PaymentMethod::class);

        $paymentMethod = $this->paymentService->addPaymentMethod(
            $request->user(),
            $request->validated('payment_method_id'),
            $request->enum('provider', PaymentProviderEnum::class)
        );

        return new PaymentMethodResource($paymentMethod);
    }

    /**
     * Process a payment.
     */
    public function purchase(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $result = $this->paymentService->processPayment(
                ProcessPaymentDTO::make([
                    'user' => $request->user(),
                    'amount' => $request->integer('amount'),
                    'currency' => $request->validated('currency', 'USD'),
                    'paymentMethodId' => $request->validated('payment_method_id'),
                    'saveCard' => $request->boolean('save_card'),
                    'provider' => $request->enum('provider', PaymentProviderEnum::class),
                    'idempotencyKey' => $request->header('Idempotency-Key'),
                ])
            );

            return response()->json($result->toArray(), Response::HTTP_CREATED);
        } catch (PaymentGatewayException $e) {
            report($e);

            return response()->json(['error' => 'Payment processing failed. Please try again later.'], 500);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
