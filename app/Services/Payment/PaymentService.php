<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\Repositories\PaymentCustomerRepositoryInterface;
use App\Contracts\Repositories\PaymentMethodRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Services\Payment\PaymentGatewayInterface;
use App\DTO\Payment\GatewaySetupIntentResultDTO;
use App\DTO\Payment\PaymentChargeDTO;
use App\DTO\Payment\PaymentMethodCreateDTO;
use App\DTO\Payment\PaymentUpdateDTO;
use App\DTO\Payment\ProcessPaymentDTO;
use App\DTO\Payment\ProcessPaymentResultDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Events\Payment\PaymentMethodAdded;
use App\Events\Payment\PaymentProcessed;
use App\Events\Payment\PaymentProcessingFailed;
use App\Events\Payment\PayoutMethodDeleted;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Models\Payment;
use App\Models\PaymentCustomer;
use App\Models\PaymentMethod;
use App\Models\PayoutMethod;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayFactory $paymentGatewayFactory,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentCustomerRepositoryInterface $paymentCustomerRepository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function setGateway(PaymentProviderEnum $provider): self
    {
        $this->paymentGateway = $this->paymentGatewayFactory->make($provider);

        return $this;
    }

    /**
     * @throws PaymentGatewayException
     * @throws Throwable
     */
    public function processPayment(ProcessPaymentDTO $dto): ProcessPaymentResultDTO
    {
        if ($dto->idempotencyKey) {
            $existingPayment = $this->paymentRepository->findByIdempotencyKey($dto->idempotencyKey, $dto->user->id);

            if ($existingPayment instanceof Payment) {
                return ProcessPaymentResultDTO::from([
                    'paymentId' => $existingPayment->id,
                    'status' => $existingPayment->status,
                    'message' => 'Replayed result for idempotent request.',
                ]);
            }
        }

        return DB::transaction(function () use ($dto): ProcessPaymentResultDTO {
            $this->setGateway($dto->provider);
            $customerId = $this->ensureCustomer($dto->user, $dto->provider);

            $paymentMethod = null;

            // Resolve Payment Method
            if ($dto->paymentMethodId) {
                $paymentMethod = $this->paymentMethodRepository->findById($dto->paymentMethodId);
            }

            // Update DTO with resolved payment method ID for model creation
            $dto->resolvedPaymentMethodId = $paymentMethod?->id;

            $payment = $this->paymentRepository->create($dto);

            try {
                $result = $this->paymentGateway->charge(
                    PaymentChargeDTO::from([
                        'user' => $dto->user,
                        'amount' => $dto->amount,
                        'currency' => $dto->currency,
                        'customerId' => $customerId,
                        'paymentMethod' => $paymentMethod,
                        'paymentMethodToken' => $dto->paymentMethodToken,
                        'idempotencyKey' => $dto->idempotencyKey,
                        'saveCard' => $dto->saveCard,
                        'returnUrl' => route('home'),
                    ])
                );

                $status = match ($result->status) {
                    'succeeded' => PaymentStatusEnum::SUCCEEDED,
                    'processing' => PaymentStatusEnum::PROCESSING,
                    'requires_action', 'requires_payment_method' => PaymentStatusEnum::REQUIRES_ACTION,
                    default => PaymentStatusEnum::FAILED,
                };

                $this->paymentRepository->updateStatus(
                    $payment,
                    PaymentUpdateDTO::from([
                        'status' => $status,
                        'transactionId' => $result->transactionId,
                        'metadata' => ['client_secret' => $result->clientSecret],
                    ])
                );

                event(new PaymentProcessed($payment, $dto->user));

                return ProcessPaymentResultDTO::from([
                    'paymentId' => $payment->id,
                    'status' => $payment->status,
                ]);
            } catch (PaymentGatewayException $paymentGatewayException) {
                $this->paymentRepository->updateStatus(
                    $payment,
                    PaymentUpdateDTO::from([
                        'status' => PaymentStatusEnum::FAILED,
                        'metadata' => ['error_message' => $paymentGatewayException->getMessage()],
                    ])
                );

                event(new PaymentProcessingFailed($payment, $dto->user, $paymentGatewayException->getMessage()));

                throw $paymentGatewayException;
            }
        });
    }

    /**
     * @throws BindingResolutionException
     */
    public function createSetupIntent(User $user, PaymentProviderEnum $provider): GatewaySetupIntentResultDTO
    {
        $this->setGateway($provider);
        $customerId = $this->ensureCustomer($user, $provider);

        return $this->paymentGateway->createSetupIntent($customerId);
    }

    /**
     * @throws BindingResolutionException
     */
    public function addPaymentMethod(User $user, string $paymentMethodId, PaymentProviderEnum $provider): PaymentMethod
    {
        $this->setGateway($provider);
        $customerId = $this->ensureCustomer($user, $provider);

        try {
            $stripePaymentMethod = $this->paymentGateway->attachPaymentMethod($paymentMethodId, $customerId);
        } catch (Exception) {
            $stripePaymentMethod = $this->paymentGateway->retrievePaymentMethod($paymentMethodId);
        }

        $expiresAt = Date::createFromDate($stripePaymentMethod->card->exp_year, $stripePaymentMethod->card->exp_month, 1)->endOfMonth();

        $paymentMethod = $this->paymentMethodRepository->create(
            PaymentMethodCreateDTO::from([
                'userId' => $user->id,
                'type' => $stripePaymentMethod->type,
                'provider' => $provider,
                'providerId' => $stripePaymentMethod->id,
                'lastFour' => $stripePaymentMethod->card->last4 ?? null,
                'brand' => $stripePaymentMethod->card->brand ?? null,
                'expiresAt' => $expiresAt,
            ])
        );

        event(new PaymentMethodAdded($paymentMethod, $user));

        return $paymentMethod;
    }

    /**
     * @throws BindingResolutionException
     */
    public function addPayoutMethod(User $user, PaymentProviderEnum $provider, string $token, string $type): PayoutMethod
    {
        $this->setGateway($provider);
        $customerId = $this->ensureCustomer($user, $provider);

        $externalAccount = $this->paymentGateway->createExternalAccount($customerId, $token, $type);

        /** @var PayoutMethod $payoutMethod */
        $payoutMethod = $user->payoutMethods()->create([
            'provider' => $provider->value,
            'provider_id' => $externalAccount->id,
            'type' => $externalAccount->type,
            'details' => [
                'last4' => $externalAccount->last4,
                'bank_name' => $externalAccount->bankName,
                'brand' => $externalAccount->brand,
            ],
        ]);

        return $payoutMethod;
    }

    /**
     * @throws BindingResolutionException
     */
    public function deletePayoutMethod(User $user, PayoutMethod $payoutMethod): void
    {
        $this->setGateway($payoutMethod->provider);
        $customerId = $this->ensureCustomer($user, $payoutMethod->provider);

        $this->paymentGateway->deleteExternalAccount($customerId, $payoutMethod->provider_id);

        $payoutMethod->delete();

        event(new PayoutMethodDeleted($payoutMethod, $user));
    }

    public function ensureCustomer(User $user, PaymentProviderEnum $provider): string
    {
        $paymentCustomer = $this->paymentCustomerRepository->findByUserIdAndProvider($user->id, $provider);

        if ($paymentCustomer instanceof PaymentCustomer) {
            return $paymentCustomer->provider_customer_id;
        }

        $customerId = $this->paymentGateway->createCustomer($user);

        $this->paymentCustomerRepository->create($user->id, $provider, $customerId);

        return $customerId;
    }

    public function linkPaymentToOrder(string $paymentId, int $orderId): void
    {
        $this->paymentRepository->linkToOrder($paymentId, $orderId);
    }
}
