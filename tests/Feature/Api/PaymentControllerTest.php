<?php

use App\DTO\Payment\GatewayChargeResultDTO;
use App\DTO\Payment\GatewaySetupIntentResultDTO;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Payment\Gateways\FakePaymentGateway;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

beforeEach(function (): void {
    Config::set('services.stripe.secret', 'sk_test_123');
    Config::set('services.payments.default_provider', 'fake');

    $this->gateway = $this->mock(FakePaymentGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSetupIntent')->andReturn(
            new GatewaySetupIntentResultDTO('cs_fake', 'si_fake')
        );
        $mock->shouldReceive('charge')->andReturn(
            new GatewayChargeResultDTO('pi_fake', 'succeeded', 1000, 'USD', 'pi_fake_secret')
        );
        $mock->shouldReceive('createCustomer')->andReturn('cus_fake');
        $mock->shouldReceive('attachPaymentMethod')->andReturn(
            (object) ['id' => 'pm_fake', 'card' => (object) ['exp_year' => 2030, 'exp_month' => 12, 'last4' => '4242', 'brand' => 'visa'], 'type' => 'card']
        );
        $mock->shouldReceive('retrievePaymentMethod')->andReturn(
            (object) ['id' => 'pm_fake', 'card' => (object) ['exp_year' => 2030, 'exp_month' => 12, 'last4' => '4242', 'brand' => 'visa'], 'type' => 'card']
        );
    });
});

it('can list payment methods', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->getJson(route('api.payments.methods.index'));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $method->id]);
});

it('can create setup intent', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('api.payments.setup-intent'), ['provider' => 'fake']);

    $response->assertCreated()
        ->assertJsonStructure(['clientSecret', 'id']);
});

it('can process purchase', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withHeader('Idempotency-Key', 'test-key-1')
        ->postJson(route('api.payments.purchase'), [
            'amount' => 1000,
            'currency' => 'USD',
            'payment_method_id' => 'pm_fake',
            'provider' => 'fake',
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['paymentId', 'status'])
        ->assertJson(['status' => 'succeeded']);

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'amount' => 1000,
        'status' => 'succeeded',
    ]);
});

it('validates purchase request', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withHeader('Idempotency-Key', 'test-key-2')
        ->postJson(route('api.payments.purchase'), ['provider' => 'fake']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['amount', 'currency', 'payment_method_id']);
});
