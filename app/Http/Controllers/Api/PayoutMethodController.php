<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\PayoutMethodRepositoryInterface;
use App\Enums\Payment\PaymentProviderEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayoutMethodRequest;
use App\Http\Resources\PayoutMethodResource;
use App\Models\PayoutMethod;
use App\Services\Payment\PaymentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PayoutMethodController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PayoutMethodRepositoryInterface $payoutMethodRepository,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $payoutMethods = $this->payoutMethodRepository->getForUser($request->user());

        return PayoutMethodResource::collection($payoutMethods);
    }

    /**
     * @throws BindingResolutionException
     */
    public function store(StorePayoutMethodRequest $request): PayoutMethodResource
    {
        $payoutMethod = $this->paymentService->addPayoutMethod(
            $request->user(),
            $request->enum('provider', PaymentProviderEnum::class),
            $request->validated('token'),
            $request->validated('type')
        );

        return PayoutMethodResource::make($payoutMethod);
    }

    /**
     * @throws AuthorizationException
     * @throws BindingResolutionException
     */
    public function destroy(Request $request, PayoutMethod $payoutMethod): Response
    {
        $this->authorize('delete', $payoutMethod);

        $this->paymentService->deletePayoutMethod($request->user(), $payoutMethod);

        return response()->noContent();
    }
}
