<?php

use App\Actions\PurchaseAction;
use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\CartItemDTO;
use App\DTO\PurchaseDTO;
use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentTypeEnum;
use App\Exceptions\Payment\PaymentGatewayException;
use App\Jobs\ProcessPayoutsJob;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Spatie\LaravelData\DataCollection;
use Stripe\ApiRequestor;
use Stripe\Exception\InvalidRequestException;
use Stripe\HttpClient\ClientInterface;

beforeEach(function (): void {
    Bus::fake([ProcessPayoutsJob::class]);
    config(['services.stripe.secret' => 'sk_test_fake']);

    // Mock Stripe HTTP Client
    $mockClient = new class implements ClientInterface
    {
        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
        {
            if (str_contains($absUrl, '/payment_methods')) {
                return [
                    '{"id": "pm_card_visa", "object": "payment_method", "type": "card", "card": {"last4": "4242", "brand": "visa", "exp_month": 12, "exp_year": 2030}}',
                    200,
                    ['Content-Type' => 'application/json'],
                ];
            }

            if (str_contains($absUrl, '/customers')) {
                return [
                    '{"id": "cus_123", "object": "customer"}',
                    200,
                    ['Content-Type' => 'application/json'],
                ];
            }

            if (str_contains($absUrl, '/payment_intents')) {
                return [
                    '{"id": "pi_123", "object": "payment_intent", "amount": 1000, "currency": "usd", "status": "succeeded", "client_secret": "pi_123_secret_456"}',
                    200,
                    ['Content-Type' => 'application/json'],
                ];
            }

            return ['{}', 200, []];
        }
    };

    ApiRequestor::setHttpClient($mockClient);
});

it('creates order with external payment and does not withdraw from balance', function (): void {
    Mail::fake();
    Notification::fake();

    $user = User::factory()->create(['balance' => 0]);
    $product = Product::factory()->create(['price' => 1000, 'stock' => 10]);
    $payment = Payment::factory()->create([
        'user_id' => $user->id,
        'amount' => 1000,
        'status' => 'succeeded',
    ]);

    $balanceServiceMock = $this->spy(BalanceServiceInterface::class);

    $action = resolve(PurchaseAction::class);

    $dto = new PurchaseDTO(
        buyer: $user,
        cart: new DataCollection(CartItemDTO::class, [['product_id' => $product->id, 'quantity' => 1]]),
        paymentType: PaymentTypeEnum::CARD,
        paymentMethodId: 'pm_card_visa',
        paymentProvider: PaymentProviderEnum::STRIPE // Use string ID for Stripe
    );

    $action->execute($dto);

    $balanceServiceMock->shouldNotHaveReceived('withdraw');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total_amount' => 1000,
    ]);

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'amount' => 1000,
        'provider' => 'stripe',
        'status' => 'succeeded',
    ]);
});

it('throws exception if payment is not successful', function (): void {
    // Override Mock to return failure
    $mockClient = new class implements ClientInterface
    {
        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): void
        {
            throw new InvalidRequestException('Payment failed', 400);
        }
    };
    ApiRequestor::setHttpClient($mockClient);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock' => 10]);

    $dto = new PurchaseDTO(
        buyer: $user,
        cart: new DataCollection(CartItemDTO::class, [['product_id' => $product->id, 'quantity' => 1]]),
        paymentType: PaymentTypeEnum::CARD,
        paymentMethodId: 'pm_card_visa',
        paymentProvider: PaymentProviderEnum::STRIPE
    );

    $action = resolve(PurchaseAction::class);
    $action->execute($dto);
})->throws(PaymentGatewayException::class);
