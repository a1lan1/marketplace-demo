<?php

declare(strict_types=1);

namespace App\Listeners\Payment;

use App\Events\Payment\FundsDeposited;
use App\Notifications\Balance\FundsDepositedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFundsDepositedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FundsDeposited $event): void
    {
        $event->user->notify(new FundsDepositedNotification($event->amount));
    }
}
