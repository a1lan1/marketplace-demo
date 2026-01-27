<?php

declare(strict_types=1);

namespace App\Contracts\Services\Payment;

use App\DTO\Payment\ExternalAccountDTO;
use App\DTO\Payment\GatewayChargeResultDTO;
use App\DTO\Payment\GatewaySetupIntentResultDTO;
use App\DTO\Payment\PaymentChargeDTO;
use App\DTO\Payment\PayoutResultDTO;
use App\Models\PayoutMethod;
use App\Models\User;
use Cknow\Money\Money;
use Stripe\PaymentMethod;

interface PaymentGatewayInterface
{
    public function charge(PaymentChargeDTO $dto): GatewayChargeResultDTO;

    public function createSetupIntent(string $customerId): GatewaySetupIntentResultDTO;

    public function createCustomer(User $user): string;

    public function attachPaymentMethod(string $paymentMethodId, string $customerId): PaymentMethod;

    public function retrievePaymentMethod(string $paymentMethodId): PaymentMethod;

    public function createPayout(PayoutMethod $payoutMethod, Money $amount): PayoutResultDTO;

    public function createExternalAccount(string $customerId, string $token, string $type): ExternalAccountDTO;

    public function deleteExternalAccount(string $customerId, string $externalAccountId): void;
}
