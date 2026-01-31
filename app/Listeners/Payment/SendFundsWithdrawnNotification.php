<?php

declare(strict_types=1);

namespace App\Listeners\Payment;

use App\Events\Payment\FundsWithdrawn;
use App\Notifications\Balance\FundsWithdrawnNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFundsWithdrawnNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FundsWithdrawn $event): void
    {
        $event->user->notify(new FundsWithdrawnNotification(
            amount: $event->amount,
            payoutId: $event->payoutId
        ));
    }
}
