<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Requests;

use App\Enums\Payment\PaymentProviderEnum;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\StorePayoutMethodRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

test('deposit request validates correctly', function (): void {
    $data = [
        'amount' => 1000,
        'currency' => 'USD',
        'payment_method_id' => 'pm_123',
        'provider' => PaymentProviderEnum::STRIPE->value,
        'save_card' => true,
    ];

    $validator = Validator::make($data, (new DepositRequest)->rules());
    expect($validator->passes())->toBeTrue();

    // Invalid amount
    $validator = Validator::make(['amount' => 99] + $data, (new DepositRequest)->rules());
    expect($validator->fails())->toBeTrue();

    // Invalid provider
    $validator = Validator::make(['provider' => 'invalid'] + $data, (new DepositRequest)->rules());
    expect($validator->fails())->toBeTrue();
});

test('withdraw request validates correctly', function (): void {
    $user = User::factory()->create();
    $payoutMethod = PayoutMethod::factory()->create(['user_id' => $user->id]);

    $data = [
        'amount' => 1000,
        'currency' => 'USD',
        'payout_method_id' => $payoutMethod->id,
    ];

    $request = new WithdrawRequest;
    $request->setUserResolver(fn () => $user); // Inject user context

    $validator = Validator::make($data, $request->rules());
    expect($validator->passes())->toBeTrue();

    // Payout Method not owned by user
    $otherUserMethod = PayoutMethod::factory()->create();
    $validator = Validator::make(['payout_method_id' => $otherUserMethod->id] + $data, $request->rules());
    expect($validator->fails())->toBeTrue();
});

test('transfer request validates correctly', function (): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $data = [
        'email' => $recipient->email,
        'amount' => 1000,
        'currency' => 'USD',
    ];

    $request = new TransferRequest;
    $request->setUserResolver(fn () => $sender);

    $validator = Validator::make($data, $request->rules());
    expect($validator->passes())->toBeTrue();

    // Self transfer
    $validator = Validator::make(['email' => $sender->email] + $data, $request->rules());
    expect($validator->fails())->toBeTrue();
});

test('store payment method request validates correctly', function (): void {
    $data = [
        'payment_method_id' => 'pm_123',
        'provider' => PaymentProviderEnum::STRIPE->value,
    ];

    $validator = Validator::make($data, (new StorePaymentMethodRequest)->rules());
    expect($validator->passes())->toBeTrue();

    $validator = Validator::make(['provider' => 'invalid'] + $data, (new StorePaymentMethodRequest)->rules());
    expect($validator->fails())->toBeTrue();
});

test('store payout method request validates correctly', function (): void {
    $data = [
        'provider' => PaymentProviderEnum::STRIPE->value,
        'token' => 'tok_123',
        'type' => 'card',
    ];

    $validator = Validator::make($data, (new StorePayoutMethodRequest)->rules());
    expect($validator->passes())->toBeTrue();

    // Invalid type
    $validator = Validator::make(['type' => 'crypto'] + $data, (new StorePayoutMethodRequest)->rules());
    expect($validator->fails())->toBeTrue();
});
