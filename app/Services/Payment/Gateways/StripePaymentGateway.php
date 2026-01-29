<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Contracts\Services\Payment\PaymentGatewayInterface;
use App\DTO\Payment\ExternalAccountDTO;
use App\DTO\Payment\GatewayChargeResultDTO;
use App\DTO\Payment\GatewaySetupIntentResultDTO;
use App\DTO\Payment\PaymentChargeDTO;
use App\DTO\Payment\PayoutResultDTO;
use App\DTO\Payment\StripePaymentIntentParamsDTO;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Models\PayoutMethod;
use App\Models\User;
use Cknow\Money\Money;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(protected StripeClient $stripe) {}

    /**
     * @throws PaymentGatewayException
     */
    public function charge(PaymentChargeDTO $dto): GatewayChargeResultDTO
    {
        try {
            $options = [];

            if ($dto->idempotencyKey) {
                $options['idempotency_key'] = $dto->idempotencyKey;
            }

            $params = StripePaymentIntentParamsDTO::fromChargeDTO($dto)->toStripeArray();
            $paymentIntent = $this->stripe->paymentIntents->create($params, $options);

            return GatewayChargeResultDTO::from([
                'transactionId' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * @throws PaymentGatewayException
     */
    public function createSetupIntent(string $customerId): GatewaySetupIntentResultDTO
    {
        try {
            $setupIntent = $this->stripe->setupIntents->create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
            ]);

            return GatewaySetupIntentResultDTO::from([
                'clientSecret' => $setupIntent->client_secret,
                'id' => $setupIntent->id,
            ]);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error creating setup intent: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * @throws PaymentGatewayException
     */
    public function createCustomer(User $user): string
    {
        try {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => ['user_id' => (string) $user->id],
            ]);

            return $customer->id;
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error creating customer: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * @throws PaymentGatewayException
     */
    public function attachPaymentMethod(string $paymentMethodId, string $customerId): PaymentMethod
    {
        try {
            return $this->stripe->paymentMethods->attach($paymentMethodId, ['customer' => $customerId]);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error attaching payment method: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * @throws PaymentGatewayException
     */
    public function retrievePaymentMethod(string $paymentMethodId): PaymentMethod
    {
        try {
            return $this->stripe->paymentMethods->retrieve($paymentMethodId);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error retrieving payment method: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * @throws PaymentGatewayException
     */
    public function createPayout(PayoutMethod $payoutMethod, Money $amount): PayoutResultDTO
    {
        try {
            $payout = $this->stripe->payouts->create([
                'amount' => (int) $amount->getAmount(),
                'currency' => $amount->getCurrency()->getCode(),
                'destination' => $payoutMethod->provider_id,
            ]);

            return PayoutResultDTO::from([
                'payoutId' => $payout->id,
                'status' => $payout->status,
                'amount' => $payout->amount,
                'currency' => $payout->currency,
                'arrivalDate' => $payout->arrival_date ? date('Y-m-d H:i:s', $payout->arrival_date) : null,
            ]);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error creating payout: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * @throws PaymentGatewayException
     */
    public function createExternalAccount(string $customerId, string $token, string $type): ExternalAccountDTO
    {
        try {
            $externalAccount = $this->stripe->customers->createSource($customerId, [
                'source' => $token,
            ]);

            return ExternalAccountDTO::from([
                'id' => $externalAccount->id,
                'type' => $externalAccount->object === 'bank_account' ? 'bank_account' : 'card',
                'last4' => $externalAccount->last4 ?? null,
                'bankName' => $externalAccount->bank_name ?? null,
                'brand' => $externalAccount->brand ?? null,
            ]);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error creating external account: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * @throws PaymentGatewayException
     */
    public function deleteExternalAccount(string $customerId, string $externalAccountId): void
    {
        try {
            $this->stripe->customers->deleteSource($customerId, $externalAccountId);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentGatewayException('Stripe API error deleting external account: '.$apiErrorException->getMessage(), $apiErrorException->getCode(), $apiErrorException);
        }
    }
}
