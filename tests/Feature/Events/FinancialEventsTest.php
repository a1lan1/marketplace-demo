<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Events\Payment\FundsDeposited;
use App\Events\Payment\FundsTransferred;
use App\Events\Payment\FundsWithdrawn;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;

test('FundsDeposited event has correct data', function (): void {
    $user = User::factory()->make();
    $amount = Money::USD(1000);
    $transaction = Transaction::factory()->make();

    $event = new FundsDeposited($transaction, $user, $amount);

    expect($event->user)->toBe($user)
        ->and($event->amount)->toBe($amount)
        ->and($event->transaction)->toBe($transaction);
});

test('FundsWithdrawn event has correct data', function (): void {
    $user = User::factory()->make();
    $amount = Money::USD(1000);
    $transaction = Transaction::factory()->make();
    $payoutId = 'po_123';

    $event = new FundsWithdrawn($transaction, $user, $amount, $payoutId);

    expect($event->user)->toBe($user)
        ->and($event->amount)->toBe($amount)
        ->and($event->transaction)->toBe($transaction)
        ->and($event->payoutId)->toBe($payoutId);
});

test('FundsTransferred event has correct data', function (): void {
    $sender = User::factory()->make();
    $recipient = User::factory()->make();
    $amount = Money::USD(1000);
    $transaction = Transaction::factory()->make();

    $event = new FundsTransferred($transaction, $sender, $recipient, $amount);

    expect($event->sender)->toBe($sender)
        ->and($event->recipient)->toBe($recipient)
        ->and($event->amount)->toBe($amount)
        ->and($event->transaction)->toBe($transaction);
});
