<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Events\Payment\FundsDeposited;
use App\Events\Payment\FundsWithdrawn;
use App\Jobs\LogActivityJob;
use App\Listeners\LogActivityListener;
use App\Models\Transaction;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Facades\Bus;

test('LogActivityListener dispatches log job for FundsDeposited event', function (): void {
    Bus::fake();

    $user = User::factory()->make();
    $amount = Money::USD(1000);
    $transaction = Transaction::factory()->make();
    $event = new FundsDeposited($transaction, $user, $amount);

    (new LogActivityListener)->handle($event);

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) use ($event): bool {
        return $job->description === $event->getDescription();
    });
});

test('LogActivityListener dispatches log job for FundsWithdrawn event', function (): void {
    Bus::fake();

    $user = User::factory()->make();
    $amount = Money::USD(1000);
    $transaction = Transaction::factory()->make();
    $payoutId = 'po_123';
    $event = new FundsWithdrawn($transaction, $user, $amount, $payoutId);

    (new LogActivityListener)->handle($event);

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) use ($event): bool {
        return $job->description === $event->getDescription();
    });
});
