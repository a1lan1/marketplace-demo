<?php

declare(strict_types=1);

namespace App\Services\PaymentProcessors;

use App\Contracts\Services\Payment\PaymentProcessorInterface;
use App\DTO\Payment\ProcessPaymentDTO;
use App\DTO\PurchaseDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Events\Payment\PaymentMethodSavedDuringPurchase;
use App\Events\Payment\PaymentMethodSaveFailedDuringPurchase;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use Cknow\Money\Money;
use Exception;
use InvalidArgumentException;
use Throwable;

readonly class CardPaymentProcessor implements PaymentProcessorInterface
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * @throws Throwable
     */
    public function process(PurchaseDTO $purchaseDTO, Order $order, Money $totalAmount): void
    {
        if (! $purchaseDTO->paymentMethodId) {
            throw new InvalidArgumentException('Payment method ID is required for card payments.');
        }

        if (! $purchaseDTO->paymentProvider instanceof PaymentProviderEnum) {
            throw new InvalidArgumentException('Payment provider is required for card payments.');
        }

        $result = $this->paymentService->processPayment(
            ProcessPaymentDTO::make([
                'user' => $purchaseDTO->buyer,
                'amount' => (int) $totalAmount->getAmount(),
                'currency' => 'USD',
                'paymentMethodId' => $purchaseDTO->paymentMethodId,
                'provider' => $purchaseDTO->paymentProvider,
                'saveCard' => $purchaseDTO->saveCard,
                'idempotencyKey' => null,
            ])
        );

        if ($result->status !== PaymentStatusEnum::SUCCEEDED) {
            throw new Exception('Payment failed: '.($result->message ?? 'Unknown error'));
        }

        // Link payment to order
        $this->paymentService->linkPaymentToOrder($result->paymentId, $order->id);

        // Save card if requested
        if ($purchaseDTO->saveCard) {
            try {
                $this->paymentService->addPaymentMethod(
                    $purchaseDTO->buyer,
                    $purchaseDTO->paymentMethodId,
                    $purchaseDTO->paymentProvider
                );

                event(new PaymentMethodSavedDuringPurchase($purchaseDTO->buyer));
            } catch (Exception $e) {
                report($e);
                event(new PaymentMethodSaveFailedDuringPurchase($purchaseDTO->buyer, $e->getMessage()));
            }
        }
    }
}
