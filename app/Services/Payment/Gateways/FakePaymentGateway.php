<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Contracts\Services\Payment\PaymentGatewayInterface;
use App\DTO\Payment\ExternalAccountDTO;
use App\DTO\Payment\GatewayChargeResultDTO;
use App\DTO\Payment\GatewaySetupIntentResultDTO;
use App\DTO\Payment\PaymentChargeDTO;
use App\DTO\Payment\PayoutResultDTO;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Models\PayoutMethod;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Str;
use Stripe\PaymentMethod;

class FakePaymentGateway implements PaymentGatewayInterface
{
    /**
     * @throws PaymentGatewayException
     */
    public function charge(PaymentChargeDTO $dto): GatewayChargeResultDTO
    {
        if (isset($dto->options['should_fail']) && $dto->options['should_fail']) {
            throw new PaymentGatewayException('Payment failed due to simulation.');
        }

        return GatewayChargeResultDTO::from([
            'transactionId' => 'fake_txn_'.Str::random(20),
            'status' => 'succeeded',
            'amount' => $dto->amount,
            'currency' => $dto->currency,
        ]);
    }

    public function createSetupIntent(string $customerId): GatewaySetupIntentResultDTO
    {
        return GatewaySetupIntentResultDTO::from([
            'clientSecret' => 'fake_secret_'.Str::random(20),
            'id' => 'fake_seti_'.Str::random(20),
        ]);
    }

    public function createCustomer(User $user): string
    {
        return 'fake_cus_'.Str::random(10);
    }

    public function attachPaymentMethod(string $paymentMethodId, string $customerId): PaymentMethod
    {
        return new PaymentMethod($paymentMethodId, [
            'id' => $paymentMethodId,
            'customer' => $customerId,
            'type' => 'card',
            'card' => [
                'last4' => '4242',
                'brand' => 'visa',
                'exp_month' => 12,
                'exp_year' => 2030,
            ],
        ]);
    }

    public function retrievePaymentMethod(string $paymentMethodId): PaymentMethod
    {
        return new PaymentMethod($paymentMethodId, [
            'id' => $paymentMethodId,
            'type' => 'card',
            'card' => [
                'last4' => '4242',
                'brand' => 'visa',
                'exp_month' => 12,
                'exp_year' => 2030,
            ],
        ]);
    }

    public function createPayout(PayoutMethod $payoutMethod, Money $amount): PayoutResultDTO
    {
        return PayoutResultDTO::from([
            'payoutId' => 'fake_po_'.Str::random(20),
            'status' => 'paid',
            'amount' => (int) $amount->getAmount(),
            'currency' => $amount->getCurrency()->getCode(),
            'arrivalDate' => now()->toDateTimeString(),
        ]);
    }

    public function createExternalAccount(string $customerId, string $token, string $type): ExternalAccountDTO
    {
        return ExternalAccountDTO::from([
            'id' => 'fake_ba_'.Str::random(20),
            'type' => $type,
            'last4' => '1234',
            'bankName' => 'Fake Bank',
            'brand' => 'Fake Brand',
        ]);
    }

    public function deleteExternalAccount(string $customerId, string $externalAccountId): void
    {
        //
    }
}
